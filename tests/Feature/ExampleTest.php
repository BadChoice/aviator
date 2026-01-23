<?php

test('returns a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertStatus(200);
});


test('can fetch from app store api', function() {
    //6740811766 - tmr
    //1555737702 - tm
    //6443849085 - tma
    (new \App\Services\AppStore\AppStoreSearch)->search('terminal madness');
});


test('can get ranking position for a term', function (){
    $ranking = (new \App\Services\AppStore\AppStoreSearch)->rankingPositionFor('1555737702', keyword:"lucasarts");
    dd($ranking);
});


test('can get apple ads campaings', function() {
    $campaings = \App\Services\AppleAds\AppleAds::make()->getCampaigns();
    dd($campaings);
});
