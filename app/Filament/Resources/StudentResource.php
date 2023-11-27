<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Models\City;
use App\Models\Country;
use App\Models\Room;
use App\Models\State;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->relationship(
                        name: 'user',
                        modifyQueryUsing: fn (Builder $query) => $query->where('role_id', 2)->orderBy('name'),
                    )
                    ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->name}")
                    ->searchable(['name']),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('father_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('nrc')
                    ->required()
                    ->maxLength(255),
                DatePicker::make('dob')
                    ->required(),
                TextInput::make('ph_no')
                    ->required()
                    ->maxLength(255),
                Select::make('room_id')
                    ->required()
                    ->relationship('room', fn ($query) => $query->orderBy('room_no'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->room_no} - {$record->name}"),
                Select::make('country_id')
                    ->options(Country::query()->pluck('name', 'id'))
                    ->live(),
                Select::make('state_id')
                    ->options(fn (Get $get): Collection => State::query()
                        ->where('country_id', $get('country_id'))
                        ->pluck('name', 'id'))
                    ->live(),
                Select::make('city_id')
                    ->options(fn (Get $get): Collection => City::query()
                        ->where('state_id', $get('state_id'))
                        ->pluck('name', 'id')),
                TextInput::make('address')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->groups([
                'name',
                'father_name',
            ])
            ->columns([
                TextColumn::make('No')
                    ->rowIndex(),
                TextColumn::make('user.name')
                    ->searchable()->sortable(),
                TextColumn::make('name')
                    ->searchable()->sortable(),
                TextColumn::make('father_name')
                    ->searchable()->sortable(),
                TextColumn::make('nrc')
                    ->searchable()->sortable(),
                TextColumn::make('dob')
                    ->searchable()->sortable(),
                TextColumn::make('ph_no')
                    ->searchable()->sortable(),
                TextColumn::make('room')
                    ->formatStateUsing(function ($state) {
                        $decodedState = json_decode($state, true);
                        return __("{$decodedState['name']} / Room No ({$decodedState['room_no']})");
                    })
                    ->searchable()->sortable(),
                TextColumn::make('address')
                    ->searchable()->sortable(),
                TextColumn::make('country.name')
                    ->searchable()->sortable(),
                TextColumn::make('state.name')
                    ->searchable()->sortable(),
                TextColumn::make('city.name')
                    ->searchable()->sortable(),
            ])
            ->defaultSort('name', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'view' => Pages\ViewStudent::route('/{record}'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}