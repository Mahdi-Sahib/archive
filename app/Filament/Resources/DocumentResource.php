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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
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
                Select::make('direction')
                    ->translateLabel()
                    ->options([
                        'Inbound' => App::getLocale() != 'en' ? trans('documents.document.ENUMS.DirectionEnum.' . DirectionEnum::INBOUND->value) : 'Inbound',
                        'Outbound' => App::getLocale() != 'en' ? trans('documents.document.ENUMS.DirectionEnum.' . DirectionEnum::OUTBOUND->value) : 'Outbound',
                        'Internal' => App::getLocale() != 'en' ? trans('documents.document.ENUMS.DirectionEnum.' . DirectionEnum::INTERNAL->value) : 'Internal',
                        'Undefined' => App::getLocale() != 'en' ? trans('documents.document.ENUMS.DirectionEnum.' . DirectionEnum::UNDEFINED->value) : 'Undefined',
                    ])
                    ->required(),
                Select::make('organization_id')
                    ->label('Organization')->translateLabel()
                    ->options(Organization::query()->pluck('organization_name','id'))
                    ->reactive(),
                Select::make('category_id')
                    ->label('Category')->translateLabel()
                    ->options(Category::query()->pluck('category_name','id'))
                    ->reactive(),
                TextInput::make('documents_title')->required()->translateLabel(),
                DatePicker::make('issue_date')->translateLabel(),
                DatePicker::make('expiry_date')->translateLabel(),
                Select::make('confidentiality_level')
                    ->translateLabel()
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
                TextInput::make('notes')->required()->columnSpanFull()->translateLabel(),

                FileUpload::make('files')
                    ->label('Files')->translateLabel()
                    ->multiple()
                    ->maxFiles(5)
                    ->openable()
                    ->downloadable()
                    ->maxSize(10000)
                    ->preserveFilenames()
                    ->directory('documents')
                    ->acceptedFileTypes(['application/pdf'])
                    ->getUploadedFileNameForStorageUsing(
                        fn (TemporaryUploadedFile $file): string => 'custom-prefix-' . $file->getClientOriginalName()
                    )
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('documents_title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('direction'),
                Tables\Columns\TextColumn::make('issue_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('confidentiality_level'),
                Tables\Columns\TextColumn::make('category.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('organization.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
