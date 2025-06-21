<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Vendor;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Evenement';

    protected static ?string $pluralLabel = 'Evenement';

    protected static ?string $label = 'Evenement';
    
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationUrl(): string
    {
        $event = \App\Models\Event::first(); // of `sole()` als je zeker weet dat er maar één is
        return static::getUrl('edit', ['record' => $event]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Naam')
                    ->maxLength(255)
                    ->disabled(auth()->user()->hasRole('voorraadbeheerder')),

                Forms\Components\TextInput::make('discription')
                    ->required()
                    ->label('Beschrijving')
                    ->maxLength(750)
                    ->disabled(auth()->user()->hasRole('voorraadbeheerder')),

                Forms\Components\TextInput::make('location')
                    ->required()
                    ->label('Locatie')
                    ->maxLength(255)
                    ->disabled(auth()->user()->hasRole('voorraadbeheerder')),


                Forms\Components\DatePicker::make('start_date')
                    ->required()
                    ->label('Startdatum')
                    ->disabled(auth()->user()->hasRole('voorraadbeheerder')),
                
                Forms\Components\DatePicker::make('end_date')
                    ->required()
                    ->label('Einddatum')
                    ->disabled(auth()->user()->hasRole('voorraadbeheerder')),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Naam'),
                Tables\Columns\TextColumn::make('location.name')
                    ->searchable()
                    ->label('Locatie'),
                Tables\Columns\TextColumn::make('discription')
                    ->searchable()
                    ->label('Beschrijving')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->searchable()
                    ->sortable()
                    ->label('Startdatum'),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->searchable()
                    ->sortable()
                    ->label('Einddatum'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Aangemaakt op')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Aangepast op')
                    ->toggleable(isToggledHiddenByDefault: true),
                    ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
            ])
            ->bulkActions([

                
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
            'index' => Pages\ListEvents::route('/', function () {
                $firstEvent = \App\Models\Event::query()->first();

                if ($firstEvent) {
                    return redirect(EventResource::getUrl('edit', ['record' => $firstEvent->getKey()]));
                }

                abort(404, 'Geen evenementen gevonden.');
            }),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
            
        ];
    }
}
