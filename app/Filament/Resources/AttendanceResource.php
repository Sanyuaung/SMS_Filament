<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Filament\Resources\AttendanceResource\RelationManagers;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('student_id')
                    ->required()
                    ->relationship(
                        name: 'student',
                        modifyQueryUsing: fn (Builder $query) => $query->orderBy('name'),
                    )
                    ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->name}")
                    ->searchable(['name']),
                Select::make('room_id')
                    ->required()
                    ->required()
                    ->relationship(
                        name: 'room',
                        modifyQueryUsing: fn (Builder $query) => $query->orderBy('room_no'),
                    )
                    ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->room_no} - {$record->name}")
                    ->searchable(['room_no']),
                DatePicker::make('date')
                    ->required(),
                Select::make('status')
                    ->options([
                        'Present' => 'Present',
                        'Absent' => 'Absent',
                        'Leave' => 'Leave',
                    ])
                    ->native(false)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('No')
                    ->rowIndex(),
                TextColumn::make('student')
                    ->formatStateUsing(function ($state) {
                        $decodedState = json_decode($state, true);
                        return __("{$decodedState['name']}");
                    })
                    ->searchable()->sortable(),
                TextColumn::make('room')
                    ->formatStateUsing(function ($state) {
                        $decodedState = json_decode($state, true);
                        return __("{$decodedState['name']} / Room No ({$decodedState['room_no']})");
                    })
                    ->searchable()->sortable(),
                TextColumn::make('date')
                    ->searchable()->sortable(),
                TextColumn::make('status')
                    ->searchable()->sortable(),
            ])
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
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'view' => Pages\ViewAttendance::route('/{record}'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
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