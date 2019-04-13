<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Billing\NotEnoughTicketException;

class Concert extends Model
{
    protected $guarded = [];

    protected $dates =['date'];

    public function getFormattedDateAttribute()
    {
        return $this->date->format('F j, Y');
    }

    public function getFormattedStartTimeAttribute()
    {
        return $this->date->format('g:ia');
    }
    public function getPriceInDollarAttribute()
    {
        return number_format($this->ticket_price /100, 2);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function orderTickets($email, $ticketQuantity)
    {
        $tickets = $this->tickets()->take($ticketQuantity)->get();
        if ($tickets->count() < $ticketQuantity) {
            throw new NotEnoughTicketException;
        }
        $order = $this->orders()->create([ 'email' => $email ]);
        foreach ($tickets as $ticket) {
            $order->tickets()->save($ticket);
        }
        return $order;
    }
    public function addTickets($quantity)
    {
        foreach (range(1, $quantity) as $i) {
            $this->tickets()->create([]);
        }
    }

    public function remainingTickets()
    {
        return $this->tickets()->whereNull('order_id')->count();
    }
}
