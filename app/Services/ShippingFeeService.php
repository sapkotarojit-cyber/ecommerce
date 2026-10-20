<?php

namespace App\Services;

use App\Models\ShippingAddress;
use RuntimeException;

class ShippingFeeService
{
    /** Resolve a saved address region to a configured Nepal shipping zone. */
    public function quote(ShippingAddress $address): array
    {
        $region = mb_strtolower(trim((string) $address->region));
        $region = preg_replace('/[^\pL\pN, ]/u', ' ', $region) ?? $region;
        $region = preg_replace('/\s+/', ' ', $region) ?? $region;

        if ($region === '' || preg_match('/\b(india|china|bhutan|bangladesh|usa|united states|international|foreign)\b/u', $region)) {
            throw new RuntimeException('We currently deliver only within Nepal. Please select a Nepal shipping address.');
        }

        $valley = ['kathmandu','kathmandu valley','ktm','lalitpur','patan','bhaktapur','bhatgaon','kirtipur','madhyapur thimi','thimi','tokha','budhanilkantha','tarakeshwar','chandragiri','dakshinkali','shankharapur','gokarneshwor','kageshwori manohara','nagarjun','changunarayan','suryabinayak','mahalaxmi','godawari'];
        $bagmati = ['chitwan','bharatpur','makwanpur','hetauda','kavrepalanchok','kavre','dhulikhel','panauti','sindhupalchok','chautara','dolakha','charikot','ramechhap','manthali','sindhuli','kamalamai','rasuwa','dhunche','nuwakot','bidur','dhading','dhading besi'];
        $otherNepal = ['morang','sunsari','jhapa','ilam','panchthar','taplejung','dhankuta','terhathum','tehrathum','sankhuwasabha','bhojpur','solukhumbu','okhaldhunga','khotang','udayapur','saptari','siraha','dhanusha','mahottari','sarlahi','rautahat','bara','parsa','kathmandu','lalitpur','bhaktapur','gorkha','lamjung','tanahun','kaski','pokhara','manang','mustang','parbat','syangja','myagdi','baglung','nawalpur','east nawalparasi','nawalparasi east','nawalpur','parasi','west nawalparasi','nawalparasi west','palpa','gulmi','arghakhanchi','kapilvastu','rupandehi','butwal','dang','pyuthan','rolpa','rukum east','eastern rukum','rukum west','western rukum','salyan','banke','bardiya','surkhet','dailekh','jajarkot','dolpa','jumla','kalikot','mugu','humla','achham','doti','kailali','kanchanpur','dadeldhura','baitadi','darchula','bajhang','bajura'];

        foreach ($valley as $name) {
            if (preg_match('/(^|[, ])'.preg_quote($name, '/').'([, ]|$)/u', $region)) {
                return $this->result('kathmandu_valley', 100.00);
            }
        }
        foreach ($bagmati as $name) {
            if (preg_match('/(^|[, ])'.preg_quote($name, '/').'([, ]|$)/u', $region)) {
                return $this->result('other_bagmati', 110.00);
            }
        }
        foreach ($otherNepal as $name) {
            if (preg_match('/(^|[, ])'.preg_quote($name, '/').'([, ]|$)/u', $region)) {
                return $this->result('other_nepal', 150.00);
            }
        }
        throw new RuntimeException('We could not identify the delivery district from your saved address. Please edit the address region to include your city or district in Nepal.');
    }

    private function result(string $zone, float $fee): array
    {
        return ['zone' => $zone, 'shipping_fee' => $fee, 'currency' => 'NPR'];
    }
}