<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Rating;
use App\Models\Hotels;
use App\Models\BookingRooms;
use App\Models\BookingLogs;
use App\Models\TicketType;
use App\Models\Tickets;
use App\Models\Location;
use App\User;
use App\Models\Settings;
use Helper, File, Session, Auth, Image, Hash;
use Jenssegers\Agent\Agent;
use Maatwebsite\Excel\Facades\Excel;

class HistoryController extends Controller
{

    public function booking(Request $request){
        
        $id = $request->id;
        $detailBooking = Booking::find($id);
        $items = BookingLogs::where('booking_id', $id)->orderBy('id', 'desc')->get();
        return view('history.booking', compact('items', 'detailBooking'));
    }
}
