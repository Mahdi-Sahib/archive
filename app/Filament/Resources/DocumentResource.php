<?php

namespace App\Filament\Resources;

use App\Enums\DirectionEnum;
use App\Enums\DocumentConfidentialityLevelEnum;
use App\Enums\DocumentStatusEnum;
use App\Filament\Resources\DocumentResource\Pages;
use App\Filament\Resources\DocumentResource\RelationManagers;
use App\Models\Category;
use App\Models\Document;
use App\Models\Organization;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\App;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    Select::make('category_id')
                        ->label('Category')->translateLabel()
                        ->options(Category::query()->pluck('category_name','id'))
                        ->reactive(),
                    Select::make('direction')
                        ->translateLabel()
                        ->options([
                            'Inbound' => App::getLocale() != 'en' ? trans('documents.document.ENUMS.DirectionEnum.' . DirectionEnum::INBOUND->value) : 'Inbound',
                            'Outbound' => App::getLocale() != 'en' ? trans('documents.document.ENUMS.DirectionEnum.' . DirectionEnum::OUTBOUND->value) : 'Outbound',
                            'Internal' => App::getLocale() != 'en' ? trans('documents.document.ENUMS.DirectionEnum.' . DirectionEnum::INTERNAL->value) : 'Internal',
                            'Undefined' => App::getLocale() != 'en' ? trans('documents.document.ENUMS.DirectionEnum.' . DirectionEnum::UNDEFINED->value) : 'Undefined',
                        ])
                        ->required(),
                    TextInput::make('document_number')->required()->translateLabel(),
                        ])  ->columns(3),


                Section::make()->schema([
                    Select::make('organization_id')
                        ->label('Organization')
                        ->translateLabel()
                        ->required()
                        ->options(Organization::query()->pluck('organization_name','id'))
                        ->reactive(),
                    TextInput::make('document_title')->required()->translateLabel(),
                ])->columns(2),

                Section::make()->schema([
                    DatePicker::make('issue_date')->translateLabel(),
                    DatePicker::make('expiry_date')->translateLabel(),
                ])->columns(2),

                Section::make()->schema([
                Select::make('confidentiality_level')
                    ->translateLabel()
                    ->required()
                    ->options([
                        'Public' => App::getLocale() != 'en' ? trans('documents.document.ENUMS.DocumentConfidentialityLevelEnum.' . DocumentConfidentialityLevelEnum::PUBLIC->value) : 'Public',
                        'Private' => App::getLocale() != 'en' ? trans('documents.document.ENUMS.DocumentConfidentialityLevelEnum.' . DocumentConfidentialityLevelEnum::PRIVATE->value) : 'Private',
                        'Confidential' => App::getLocale() != 'en' ? trans('documents.document.ENUMS.DocumentConfidentialityLevelEnum.' . DocumentConfidentialityLevelEnum::CONFIDENTIAL->value) : 'Confidential',
                    ]),
                Select::make('status')
                    ->translateLabel()
                    ->options([
                        'Pending' => App::getLocale() != 'en' ? trans('documents.document.ENUMS.DocumentStatusEnum.' . DocumentStatusEnum::PENDING->value) : 'Pending',
                        'Verified' => App::getLocale() != 'en' ? trans('documents.document.ENUMS.DocumentStatusEnum.' . DocumentStatusEnum::VERIFIED->value) : 'Verified',
                        'Under Review' => App::getLocale() != 'en' ? trans('documents.document.ENUMS.DocumentStatusEnum.' . DocumentStatusEnum::UNDER_REVIEW->value) : 'Under Review',
                        'Rejected' => App::getLocale() != 'en' ? trans('documents.document.ENUMS.DocumentStatusEnum.' . DocumentStatusEnum::REJECTED->value) : 'Rejected',
                        'Expired' => App::getLocale() != 'en' ? trans('documents.document.ENUMS.DocumentStatusEnum.' . DocumentStatusEnum::EXPIRED->value) : 'Expired',
                        'Archived' => App::getLocale() != 'en' ? trans('documents.document.ENUMS.DocumentStatusEnum.' . DocumentStatusEnum::ARCHIVED->value) : 'Archived',
                    ])
                    ->required(),
                ])->columns(2),
                TextInput::make('notes')->required()->columnSpanFull()->translateLabel(),

                FileUpload::make('files')
                    ->label('Files')->translateLabel()
                    ->multiple()
                    ->maxFiles(5)
                    ->openable()
                    ->downloadable()
                    ->maxSize(10000)
                    // TODO : Add the ability to customize the file name
                    ->directory('documents')
                    ->acceptedFileTypes(['application/pdf'])
                    ->columnSpanFull(),

                Forms\Components\Hidden::make('created_by')
                    ->default(auth()->id()),
                Forms\Components\Hidden::make('updated_by')
                    ->default(auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('document_title')
                    ->searchable(),
                TextColumn::make('direction'),
                TextColumn::make('document_number')
                    ->label('Number')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('category.category_name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('organization.organization_name')
                    ->sortable(),
                TextColumn::make('issue_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('expiry_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('status'),
                TextColumn::make('confidentiality_level'),
                TextColumn::make('user.name')
                    ->label('User'),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->columnSpan(6),
                        DatePicker::make('created_until')
                            ->columnSpan(6),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->columns(12),
            ])
            ->filtersFormWidth('md')





            ->defaultSort('created_at', 'desc')
            ->headerActions([

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDocuments::route('/'),
        ];
    }
}
