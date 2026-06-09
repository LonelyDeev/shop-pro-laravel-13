<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    protected $guarded = ['id'];

    public function group()
    {
        return $this->belongsTo(AttributeGroup::class, 'attribute_group_id', 'id');
    }

    public function attributeGroup()
    {
        return $this->belongsTo(AttributeGroup::class);
    }

    public function prices()
    {
        return $this->belongsToMany(Price::class, 'attribute_price', 'attribute_id', 'price_id');
    }
}
