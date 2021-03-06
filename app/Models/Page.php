<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Mockery\CountValidator\Exception;
use GrahamCampbell\Markdown\Facades\Markdown;

use App\Models\Setting;

class Page extends Model
{
    protected $table = 'pages';

    protected $fillable = [
        'title',
        'alias',
        'menu_title',
        'sub_title',
        'description',
        'menu_index',
        'published_at',

        'is_published',
        'is_menu_hide',
        'is_deleted',

        'is_alias_blocked',
        'is_allow_short_alias',
        'is_abstract',
        'is_part',

        'parent_page_id',
        'author_id',
        'template_id',
        'context_id'
    ];

    protected $casts = [
        'id' => 'integer',
        'is_published' => 'boolean',
        'is_menu_hide' => 'boolean',
        'is_deleted' => 'boolean',
        'is_abstract' => 'boolean',
        'is_part' => 'boolean',
        'is_allow_short_alias' => 'boolean',
        'is_alias_blocked' => 'boolean',
        'menu_index' => 'integer',
        'parent_page_id' => 'integer',
        'author_id' => 'integer',
        'template_id' => 'integer',
        'context_id' => 'integer'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'published_at'
    ];

    /**
     * @Relation
     */
    public function author()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @Relation
     */
    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * @Relation
     */
    public function context()
    {
        return $this->belongsTo(Context::class);
    }

    /**
     * @Relation
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'pages_tags');
    }

    /**
     * @Relation
     */
    public function entity_translation()
    {
        return $this->morphOne(EntityTranslation::class, 'entity');
    }

    /**
     * @Relation
     */

    public function parent_page()
    {
        return $this->belongsTo(Page::class, 'parent_page_id');
    }
    /**
     * @Relation
     */
    public function child_pages()
    {
        return $this->hasMany(Page::class, 'parent_page_id')->with('child_pages');
    }
    /**
     * @Relation
     */
    public function child_pages_by_index()
    {
        return $this->hasMany(Page::class, 'parent_page_id')->orderBy('menu_index', 'ASC')->with('child_pages_by_index');
    }
    /**
     * @Relation
     */
    public function published_child_pages()
    {
        return $this->hasMany(Page::class, 'parent_page_id')->where(['is_deleted' => false, 'is_published' => true])->with('published_child_pages');
    }
    /**
     * @Relation
     */
    public function published_child_pages_by_index()
    {
        return $this->hasMany(Page::class, 'parent_page_id')->orderBy('menu_index', 'ASC')->where(['is_deleted' => false, 'is_published' => true])->with('published_child_pages_by_index');
    }
    /**
     * @Relation
     */
    public function published_child_pages_by_date()
    {
        return $this->hasMany(Page::class, 'parent_page_id')->orderBy('created_at', 'DESC')->where(['is_deleted' => false, 'is_published' => true])->with('published_child_pages_by_date');
    }

    /**
     * @Relation
     */
    public function logs()
    {
        return $this->morphMany(UserActionLog::class, 'logable');
    }

    /**
     * @Relation
     */
    public function seo()
    {
        return $this->hasOne(PageSEO::class, 'page_id');
    }

    public function getSubFieldsValuesAttribute()
    {
        $dictionary = \DB::table('sub_fields_values')->where('page_id', $this->id)
            ->join('sub_fields', 'sub_fields_values.sub_field_id', '=', 'sub_fields.id')
            ->pluck('sub_fields_values.value', 'sub_fields.key')->toArray();

        //make undefined sub_fields as empty strings
        foreach($this->template->sub_fields as $sub_field)
            if(!isset($dictionary[$sub_field->key]))
                $dictionary[$sub_field->key] = '';

        return $dictionary;
    }

    public function getAliasAttribute()
    {
        return isset($this->attributes['alias']) ? $this->attributes['alias'] : $this->id;
    }

    public function getNameAttribute()
    {
        return $this->title;
    }

    public function getMenuTitleAttribute()
    {
        return isset($this->attributes['menu_title']) ? $this->attributes['menu_title'] : $this->title;
    }

    public function setTagsIdsAttribute($value)
    {
        $this->tags()->sync($value);
    }
    public function getTagsIdsAttribute()
    {
        $ids = [];
        foreach($this->tags as $tag)
            $ids[] = $tag->id;
        return $ids;
    }

    private function contentRow(){
        return \DB::table('pages_contents')->where('page_id', $this->id)->first();
    }
    public function getContentTextAttribute()
    {
        $content_row = $this->contentRow();
        if($content_row)
            return $content_row->value;
        else
            return '';
    }
    public function getContentHtmlAttribute()
    {
        $is_markdown_mode = Setting::where('key', 'markdown_mode')->first()->value;

        if($is_markdown_mode == 1)
            return Markdown::convertToHtml($this->content_text);
        else
            return $this->content_text;
    }

    public function setContentTextAttribute($value)
    {
        $content_row = $this->contentRow();
        if($content_row)
            \DB::table('pages_contents')->where('page_id', $this->id)->update(['value' => $value, 'updated_at' => new \DateTime()]);
        else if($value)
            \DB::table('pages_contents')->insert(
                ['page_id' => $this->id, 'value' => $value, 'created_at' => new \DateTime()]
            );
    }

    public function getPageUriAttribute(){
        if($this->is_abstract && count($this->child_pages))
            return $this->child_pages[0]->page_uri;
        else if($this->is_part && $this->parent_page_id)
            return $this->parent_page->page_uri . '#' . $this->alias;
        else if($this->parent_id)
            return $this->parent->page_uri . '/' .$this->alias;
        else
            return $this->alias;
    }

    public function getSeoTitleAttribute(){
        if($this->seo && $this->seo->title)
            return $this->seo->title;
        else
            return $this->title;
    }

    public function getSeoDescriptionAttribute(){
        if($this->seo && $this->seo->description)
            return $this->seo->description;
        else
            return $this->description;
    }

    public function getSeoKeywordsAttribute(){
        if($this->seo && $this->seo->keywords)
            return $this->seo->keywords;
        else
            return '';
    }

    public function getSeoImageAttribute(){
        if($this->seo && $this->seo->image)
            return $this->seo->image;
        else
            return '';
    }

    public function getPageByTranslation($locale){
    	if(!$this->entity_translation)
    		return $this;

        return EntityTranslation::where([
            'hash_key' => $this->entity_translation->hash_key,
            'locale' => $locale
        ])->first()->entity;
    }
}
