<?php

namespace App\Filament\Pembimbing\Resources\MonitoringaktivitasMagangResource\Pages;

use App\Filament\Pembimbing\Resources\MonitoringaktivitasMagangResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;

class ViewMonitoringaktivitasMagang extends ViewRecord implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = MonitoringaktivitasMagangResource::class;

    public ?array $formData = [];

    public function mount($record): void
    {
        parent::mount($record);

        $this->form->fill([
            'feedback_progres' => $this->record->feedback_progres,
        ]);
    }

    public function getFormStatePath(): string
    {
        return 'formData';
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Textarea::make('feedback_progres')
                ->label('Feedback Dosen Pembimbing')
                ->placeholder('Tuliskan feedback Anda di sini...')
                ->rows(6)
                ->required()
                ->maxLength(500),
        ]);
    }

    public function save(): void
    {
        $this->record->update([
            'feedback_progres' => $this->formData['feedback_progres'] ?? null,
        ]);

        Notification::make()
            ->title('Feedback berhasil disimpan')
            ->success()
            ->send();
    }

    public function getFooter(): ?View
    {
        return view('filament.pembimbing.resources.monitoringaktivitas-magang-resource.pages.view-tabs', [
            'record' => $this->record,
            'form' => $this->form,
        ]);
    }
}
