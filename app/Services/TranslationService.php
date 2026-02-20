<?php

namespace App\Services;

use App\Models\Translation;
use Illuminate\Support\Collection;

class TranslationService
{
    
    public static function translate(string $tableName, $model, array $fields)
    {
        $lang = app()->getLocale();

        // Varsayılan dil (tr) ise çeviriye gerek yok
        if ($lang === 'tr') {
            return $model;
        }

        $translations = Translation::where('table_name', $tableName)
            ->where('record_id', $model->id)
            ->where('language_code', $lang)
            ->whereIn('field_name', $fields)
            ->pluck('value', 'field_name');

        // Çevirileri uygula
        foreach ($fields as $field) {
            if (isset($translations[$field])) {
                $model->$field = $translations[$field];
            }
        }

        return $model;
    }


    public static function translateMany(string $tableName, Collection $models, array $fields): Collection
    {
        $lang = app()->getLocale();

        if ($lang === 'tr' || $models->isEmpty()) {
            return $models;
        }

        // Tüm id'ler için çevirileri tek sorguda çek
        $ids = $models->pluck('id')->toArray();

        $translations = Translation::where('table_name', $tableName)
            ->whereIn('record_id', $ids)
            ->where('language_code', $lang)
            ->whereIn('field_name', $fields)
            ->get()
            ->groupBy('record_id');

        // Her modele çevirileri uygula
        foreach ($models as $model) {
            if (isset($translations[$model->id])) {
                foreach ($translations[$model->id] as $t) {
                    $model->{$t->field_name} = $t->value;
                }
            }
        }

        return $models;
    }
}