<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddLinkRequest;
use App\Link;
use Carbon\Carbon;

class HomeControler extends Controller {

    protected $carbon;
    protected $link;


    public function __construct(Link $link, Carbon $carbon) {
        $this->link = $link;
        $this->carbon = $carbon;
    }



    public function addAction(AddLinkRequest $request) {
        $lifetime = $request->get('lifetime');
        $link = $request->get('link', null);

        if($lifetime) {
            $lifetime = strtotime($lifetime);
        } else {
            $lifetime = 0;
        }

        $shortcode = $this->link->create(['link' => $link, 'lifetime' => $lifetime]);

        $hash = \Hashids::encode($shortcode->id, $lifetime);

        return route('redirect', ['hash' => $hash]);
    }



    public function redirectAction($hash) {
        try {
            list($id, $lifetime) = \Hashids::decode($hash);
        } catch(\Exception $e) {
            abort(500, 'Can not decode link!');
        }

        if(empty($id) ) {
            abort(400, 'Wrong link!');
        }

        if($lifetime && $this->carbon->timestamp > $lifetime) {
            abort(403, 'Link expired!');
        }

        $shortcode = $this->link->whereId($id)->first();

        if(empty($shortcode) ) {
            abort(404, 'Link not found!');
        }

        return redirect($shortcode->link);
    }


}
