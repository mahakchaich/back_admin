<?php

namespace App\Observers;


use App\Models\Command;


class CommandObserver
{
    /**
     * Handle the Commande "created" event.
     *
     * @param  \App\Models\Command  $command
     * @return void
     */
    public function created(Command $command)
    {
        // get paners from command
        // iterate paniers one by one and get qtn
        //substruct cart qtn from panier qtn
        $boxs = $command->boxs;
        foreach ($boxs as $box) {
            $cmd_qtn = $box->box_command->sum('quantity');
            $box->substruct($cmd_qtn, 'remaining_quantity');
            $box->save();
        }
    }

    /**
     * Handle the Command "updated" event.
     *
     * @param  \App\Models\Command $command
     * @return void
     */
    public function updated(Command $command)
    {
        //
    }

    /**
     * Handle the Commande "deleted" event.
     *
     * @param  \App\Models\Commande  $command
     * @return void
     */
    public function deleted(Command $command)
    {
        //
    }

    /**
     * Handle the Commande "restored" event.
     *
     * @param  \App\Models\Command $commande
     * @return void
     */
    public function restored(Command $command)
    {
        //
    }

    /**
     * Handle the Commande "force deleted" event.
     *
     * @param  \App\Models\Command  $commande
     * @return void
     */
    public function forceDeleted(Command $command)
    {
        //
    }
}
