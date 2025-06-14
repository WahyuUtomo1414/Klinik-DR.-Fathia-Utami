<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Doctor;
use App\Models\Status;
use App\Models\Booking;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\DoctorSchedule;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\BookingResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\BookingResource\RelationManagers;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark';

    protected static ?string $navigationGroup = 'Booking Management';

    protected static ?string $navigationLabel = 'Booking';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('code')
                    ->required()
                    ->label('Order Code')
                    ->disabled(fn ($context) => $context === 'edit')
                    ->default(function ($context) {
                        if ($context === 'create') {
                            $randomNumber = rand(100, 99999);
                            return 'BOOK-' . $randomNumber . '-' . now()->format('Ymd');
                        }
                        return null;
                    }),
                Select::make('user_id')
                    ->required()
                    ->options(fn () => User::all()->pluck('name', 'id'))
                    ->label('User')
                    ->default(auth()->user()->id)
                    ->searchable(),
                Select::make('doctor_id')
                    ->required()
                    ->searchable()
                    ->options(Doctor::with('user')->get()->pluck('user.name', 'id'))
                    ->label('Doctor Name'),
                DatePicker::make('booking_date')
                    ->required()
                    ->Label('Booking Date')
                    ->native(false)
                    ->columnSpanFull(),
                Textarea::make('complaint')
                    ->required()
                    ->columnSpanFull(),
                Hidden::make('doctor_feedback')
                    ->columnSpanFull(),
                Select::make('status_id')
                    ->required()
                    ->label('Status')
                    ->searchable()
                    ->options(Status::where('status_type_id', 2)->pluck('name', 'id')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Booking Code')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Patient')
                    ->sortable(),
                TextColumn::make('booking_date')
                    ->label('Booking Date')
                    ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->locale('id')->translatedFormat('l, d F Y'))
                    ->sortable(),
                TextColumn::make('doctor.user.name')
                    ->label('Doctor Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('queue_number')
                    ->label('Queqeu Date')
                    ->sortable(),
                TextColumn::make('complaint')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('doctor_feedback')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('pdf_path')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('status.name')
                    ->label('Status')
                    ->sortable(),
                TextColumn::make('createdBy.name')
                    ->label('Created By'),
                TextColumn::make('updatedBy.name')
                    ->label("Updated by"),
                TextColumn::make('deletedBy.name')
                    ->label("Deleted by"),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
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
