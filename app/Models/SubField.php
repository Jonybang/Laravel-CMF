<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubField extends Model
{
    protected $table = 'sub_fields';
    protected $casts = [
        'config' => 'array',
    ];

    protected $fillable = [
        'key',
        'name',
        'description',
        'config',
        'default_value',
        'sub_field_type_id'
    ];

    /**
     * @Relation
     */
    public function templates()
    {
        return $this->belongsToMany(Template::class, 'templates_sub_fields');
    }

    public function setTemplatesIdsAttribute($value)
    {
        $this->templates()->sync($value);
    }
    public function getTemplatesIdsAttribute()
    {
        $ids = [];
        foreach($this->templates as $template)
            $ids[] = $template->id;
        return $ids;
    }

    /**
     * @Relation
     */
    public function type()
    {
        return $this->belongsTo(SubFieldType::class, 'sub_field_type_id', 'id');
    }
    /**
     * @Relation
     */
    public function values()
    {
        return $this->hasMany(SubFieldValue::class, 'sub_field_id');
    }

    public function getTitleAttribute(){
        return $this->attributes['title'] ? $this->attributes['title'] : $this->attributes['name'];
    }
}
