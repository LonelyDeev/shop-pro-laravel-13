<?php

namespace App\Http\Requests\Back\Post;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'post_type'          => 'required|in:text,video,podcast',
            'title'              => 'required|string|max:191',
            'summary'            => 'nullable|string',
            'content'            => 'nullable|string',
            'categories'        => 'required|exists:categories,id',
            'published'          => 'nullable',
            'image'              => 'image',
            'slug'               => 'nullable|unique:posts,slug,' . $this->post->id,
            'publish_date'       => 'nullable|date',
            'meta_title'         => 'nullable',
            'meta_description'   => 'nullable',
            'video_url'          => 'nullable|url|required_if:post_type,video',
            'podcast_url'        => 'nullable|url|required_if:post_type,podcast',
            'is_editor_pick'     => 'nullable|boolean',
            'allow_comments'     => 'nullable|boolean',
        ];
    }
}
