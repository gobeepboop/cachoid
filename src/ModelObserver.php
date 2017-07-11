<?php

namespace Beep\Cachoid;

use Illuminate\Database\Eloquent\Model;

class ModelObserver
{
    /**
     * Handle the created event for the model.
     *
     * @param  Model|Cacheable  $model
     * @return void
     */
    public function created($model): void
    {
        $model->cacheable();
    }

    /**
     * Handle the updated event for the model.
     *
     * @param  Model|Cacheable  $model
     * @return void
     */
    public function updated($model): void
    {
        $model->cacheable();
    }

    /**
     * Handle the deleted event for the model.
     *
     * @param  Model|Cacheable  $model
     * @return void
     */
    public function deleted($model): void
    {
        $model->bustable();
    }

    /**
     * Handle the saved event for the model.
     *
     * @param  Model|Cacheable  $model
     * @return void
     */
    public function restored($model): void
    {
        $model->cacheable();
    }
}
