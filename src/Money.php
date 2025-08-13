<?php

namespace WallaceMaxters\FilamentBrlInput;

use Illuminate\Support\Js;
use Filament\Forms\Components\TextInput;

class Money extends TextInput
{
    protected function setUp(): void
    {
        $this
            ->maxLength(20)
            ->extraInputAttributes($this->onInput(...), true)
            ->inputMode('decimal')
            ->formatStateUsing(fn ($state): string => $this->hydrateCurrency($state))
            ->dehydrateStateUsing(fn ($state): float => $this->dehydrateCurrency($state))
            ->prefix('R$');
    }

    protected function onInput(): array
    {
        return [
            'x-on:input' => <<<'JS'
            (event) => {

                let value = parseInt(event.target.value.replace(/\D/g, '')) || 0;

                value = (value / 100).toFixed(2);

                value = value
                    .replace('.', ',')
                    .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    
                $set($statePath, value, true, false);
            }
            JS,
        ];
    }

    public function dehydrateCurrency(?string $state): float
    {
        $result = str($state)->swap([
            '.' => '',
            ',' => ''
        ])->toString();

        return is_numeric($result) ? $result / 100 : 0;
    }

    public function hydrateCurrency($value): string
    {
        $rounded = round((float) $value, 2, PHP_ROUND_HALF_DOWN);

        return number_format($rounded, 2, ',', '.');
    }
}
