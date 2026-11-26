<?php

namespace App\Concerns;

use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Reglas compartidas para identificar un versículo en las APIs de notas y
 * favoritos. Escritura y borrado deben validar exactamente lo mismo.
 */
trait VerseReferenceValidationRules
{
    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    protected function verseReferenceRules(): array
    {
        return [
            'book_code' => ['required', 'string', 'max:10'],
            'chapter' => ['required', 'integer', 'min:1'],
            'verse' => ['required', 'integer', 'min:1'],
        ];
    }
}
