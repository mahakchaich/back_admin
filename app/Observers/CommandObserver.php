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
        $paniers = $command->paniers;
        foreach ($paniers as $panier) {
            $cmd_qtn = $panier->command_panier->sum('quantity');
            $panier->substruct($cmd_qtn, 'remaining_quantity');
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
