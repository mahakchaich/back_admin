<?php

namespace App\Observers;

use App\Models\Box;

class BoxObserver
{
    /**
     * Handle the Box "created" event.
     *
     * @param  \App\Models\Box  $box
     * @return void
     */
    public function created(Box $box)
    {
    }

    /**
     * Handle the Box "updated" event.
     *
     * @param  \App\Models\Box  $box
     * @return void
     */
    public function updated(Box $box)
    {
        $box->remaining_quantity <= 0 ? $box->status = 'FINISHED' : $box->status = 'PENDING';
        $box->save();
    }




    /**
     * Handle the Box "deleted" event.
     *
     * @param  \App\Models\Box  $box
     * @return void
     */
    public function deleted(Box $box)
    {
        //
    }

    /**
     * Handle the Box "restored" event.
     *
     * @param  \App\Models\Box  $box
     * @return void
     */
    public function restored(Box $box)
    {
        //
    }

    /**
     * Handle the Box "force deleted" event.
     *
     * @param  \App\Models\Box  $box
     * @return void
     */
    public function forceDeleted(Box $box)
    {
        //
    }
}
