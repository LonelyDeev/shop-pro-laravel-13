<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSetting;
use App\Models\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FormController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Form::class, 'forms');
    }
    public function index()
    {
        $forms = Form::withCount('submissions')->orderBy('created_at', 'desc')->paginate(20);
        return view('back.forms.index', compact('forms'));
    }

    public function create()
    {
        return view('back.forms.create');
    }

    public function store(Request $request)
    {
        $request->offsetSet('slug', sluggable_helper_function($request->slug ?: $request->title));
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:forms',
            'description' => 'nullable|string',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'success_message' => 'nullable|string|max:500',
            'button_text' => 'nullable|string|max:100',
            'email_notify' => 'nullable|email',
            'published' => 'nullable|boolean',
            'fields_data' => 'required|json',
        ], [
            'title.required' => 'عنوان فرم الزامی است',
            'slug.required' => 'اسلاگ فرم الزامی است',
            'slug.unique' => 'این اسلاگ قبلاً استفاده شده است',
            'slug.regex' => 'اسلاگ فقط می‌تواند شامل حروف کوچک انگلیسی، اعداد و خط تیره باشد',
            'fields_data.required' => 'حداقل یک فیلد باید به فرم اضافه شود',
        ]);

        $validated['published'] = $request->has('published');

        $form = Form::create($validated);

        // ذخیره فیلدها
        $fieldsData = json_decode($request->fields_data, true);
        foreach ($fieldsData as $index => $fieldData) {
            $form->fields()->create([
                'label' => $fieldData['label'],
                'name' => $fieldData['name'],
                'type' => $fieldData['type'],
                'required' => $fieldData['required'] ?? false,
                'placeholder' => $fieldData['placeholder'] ?? null,
                'help_text' => $fieldData['help_text'] ?? null,
                'default_value' => $fieldData['default_value'] ?? null,
                'class' => $fieldData['class'] ?? null,
                'rules_validation' => $fieldData['validation'] ?? null,
                'options' => $fieldData['options'] ?? null,
                'order' => $index + 1,
            ]);
        }

        return response('success');
    }

    // رندر فیلدها (برای AJAX)
    public function renderFields(Request $request)
    {
        $fields = json_decode($request->fields, true) ?? [];
        $typeNames = [
            'text' => 'متن ساده',
            'email' => 'ایمیل',
            'tel' => 'تلفن',
            'number' => 'شماره',
            'textarea' => 'متن چند خطی',
            'select' => 'انتخابگر',
            'checkbox' => 'چک‌باکس',
            'radio' => 'رادیویی',
            'date' => 'تاریخ',
            'file' => 'فایل',
            'password' => 'رمز عبور',
            'url' => 'لینک'
        ];

        $html = view('back.forms.partials.field-preview', compact('fields', 'typeNames'))->render();

        return response()->json(['html' => $html]);
    }

    public function edit(Form $form)
    {
        $form->load('fields');
        return view('back.forms.edit', compact('form'));
    }

    public function update(Request $request, Form $form)
    {
        $request->offsetSet('slug', sluggable_helper_function($request->slug ?: $request->title));
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:forms,slug,' . $form->id ,
            'description' => 'nullable|string',
            'published' => 'nullable|boolean',
            'success_message' => 'nullable|string|max:500',
            'button_text' => 'nullable|string|max:100',
            'email_notify' => 'nullable|email',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'fields_data' => 'required|json',
        ], [
            'title.required' => 'عنوان فرم الزامی است',
            'slug.required' => 'اسلاگ فرم الزامی است',
            'slug.unique' => 'این اسلاگ قبلاً استفاده شده است',
            'slug.regex' => 'اسلاگ فقط می‌تواند شامل حروف کوچک انگلیسی، اعداد و خط تیره باشد',
        ]);
        $maxOrder = $form->fields()->max('order') ?? 0;
        $validated['published'] = $request->has('published');
        // ذخیره فیلدها
        $fieldsData = json_decode($request->fields_data, true);
        foreach ($fieldsData as $index => $fieldData) {
            $existFields=$form->fields()->where('name',$fieldData['name'])->first();
            if ($existFields) {
                return response()->json([
                    'message' => 'خطای اعتبارسنجی رخ داده است',
                    'errors' => [
                        'name' => ['مقدار وارد شده با نام '.$fieldData['name'].' از قبل موجود است']
                    ]
                ], 422);

            }

            $form->fields()->create([
                'label' => $fieldData['label'],
                'name' => $fieldData['name'],
                'type' => $fieldData['type'],
                'required' => $fieldData['required'] ?? false,
                'placeholder' => $fieldData['placeholder'] ?? null,
                'help_text' => $fieldData['help_text'] ?? null,
                'default_value' => $fieldData['default_value'] ?? null,
                'class' => $fieldData['class'] ?? null,
                'rules_validation' => $fieldData['validation'] ?? null,
                'options' => $fieldData['options'] ?? null,
                'order' => $maxOrder + $index + 1,
            ]);
        }
        $form->update($validated);
        return response('success');
    }

    public function destroy(Form $form)
    {
        $form->tags()->detach();
        $form->delete();

        return response('success');
    }

    public function multipleDestroy(Request $request)
    {
        $this->authorize('filds.delete');

        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:forms,id',
        ]);

        foreach ($request->ids as $id) {
            $form = Form::find($id);
            $this->destroy($form);
        }

        return response('success');
    }

    // مدیریت فیلدها (API)
    public function addField(Request $request, Form $form)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'name' => 'required|string|max:255|regex:/^[a-zA-Z_][a-zA-Z0-9_]*$/|unique:form_fields,name,NULL,id,form_id,' . $form->id,
            'type' => 'required|in:text,email,tel,number,textarea,select,checkbox,radio,date,file,password,url',
            'required' => 'nullable|boolean',
            'placeholder' => 'nullable|string',
            'options' => 'nullable|array',
            'default_value' => 'nullable|string',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'help_text' => 'nullable|string',
            'class' => 'nullable|string',
            'validation_rules' => 'nullable|string',
        ], [
            'label.required' => 'عنوان فیلد الزامی است',
            'name.required' => 'نام فیلد الزامی است',
            'name.regex' => 'نام فیلد باید با حرف انگلیسی یا زیرخط شروع شود و فقط شامل حروف، اعداد و زیرخط باشد',
            'name.unique' => 'این نام فیلد قبلاً در این فرم استفاده شده است',
        ]);

        $field = $form->fields()->create([
            'label' => $validated['label'],
            'name' => $validated['name'],
            'type' => $validated['type'],
            'required' => $request->has('required'),
            'placeholder' => $validated['placeholder'] ?? null,
            'options' => $validated['options'] ?? null,
            'default_value' => $validated['default_value'] ?? null,
            'help_text' => $validated['help_text'] ?? null,
            'class' => $validated['class'] ?? null,
            'validation_rules' => $validated['validation_rules'] ?? null,
            'order' => $form->fields()->count() + 1,
        ]);

        return response()->json(['success' => true, 'field' => $field]);
    }

    public function updateField(Request $request, Form $form, FormField $field)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'name' => 'required|string|max:255|regex:/^[a-zA-Z_][a-zA-Z0-9_]*$/|unique:form_fields,name,' . $field->id . ',id,form_id,' . $form->id,
            'type' => 'required|in:text,email,tel,number,textarea,select,checkbox,radio,date,file,password,url',
            'required' => 'nullable|boolean',
            'placeholder' => 'nullable|string',
            'options' => 'nullable|array',
            'default_value' => 'nullable|string',
            'help_text' => 'nullable|string',
            'class' => 'nullable|string',
            'validation_rules' => 'nullable|string',
        ]);

        $field->update([
            'label' => $validated['label'],
            'name' => $validated['name'],
            'type' => $validated['type'],
            'required' => $request->has('required'),
            'placeholder' => $validated['placeholder'] ?? null,
            'options' => $validated['options'] ?? null,
            'default_value' => $validated['default_value'] ?? null,
            'help_text' => $validated['help_text'] ?? null,
            'class' => $validated['class'] ?? null,
            'validation_rules' => $validated['validation_rules'] ?? null,
        ]);

        return response()->json(['success' => true]);
    }

    public function deleteField(Form $form, FormField $field)
    {
        $field->delete();

        return response('success');
    }

    public function reorderFields(Request $request, Form $form)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'required|integer|exists:form_fields,id',
        ]);

        foreach ($request->order as $index => $fieldId) {
            FormField::where('id', $fieldId)
                ->where('form_id', $form->id)
                ->update(['order' => $index + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'ترتیب فیلدها با موفقیت ذخیره شد'
        ]);
    }




    public function submissions(Form $form)
    {
        $submissions = $form->submissions()->orderBy('submitted_at', 'desc')->paginate(20);
        return view('back.forms.submissions', compact('form', 'submissions'));
    }

// نمایش جزئیات یک پاسخ (Ajax)
    public function showSubmission(Form $form, FormSubmission $submission)
    {

        if (request()->ajax()) {
            $html = view('back.forms.partials.submission-detail', compact('form', 'submission'))->render();
            return response()->json(['html' => $html]);
        }

        return view('back.forms.partials.submission-detail', compact('form', 'submission'));
    }

// حذف یک پاسخ
    public function deleteSubmission(Form $form, FormSubmission $submission)
    {
        $image=json_decode($submission->data);

        if ($image->image!= null and $submission->has_photo){
            Storage::disk('public')->delete($image->image);
        }
        $submission->delete();

        return response('success');
    }

// حذف گروهی پاسخ‌ها
    public function multipleDestroySubmission(Request $request, Form $form)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:form_submissions,id',
        ]);

        foreach ($request->ids as $id) {
            $submission = FormSubmission::find($id);
            $form=Form::find($submission->form_id);
            $this->deleteSubmission($form,$submission);
        }

        return response('success');
    }


    // نمایش صفحه پیش‌نمایش فرم
    public function preview(Form $form)
    {
        // بارگذاری تنظیمات فرم
        $setting = $form->setting ?? new FormSetting(['form_id' => $form->id]);

        // بارگذاری فیلدها با ترتیب نمایش
        $fields = $form->fields()->orderBy('order')->get();

        return view('back.forms.preview', compact('form', 'setting', 'fields'));
    }

// ذخیره تنظیمات فرم
    public function saveSettings(Request $request, Form $form)
    {
        $settings = $request->validate([
            'form_position' => 'required|in:top,bottom',
            'form_width' => 'required|in:full,half,third',
            'form_alignment' => 'required|in:center,right,left',
            'form_class' => 'nullable|string',
            'custom_css' => 'nullable|string',
            'default_column_class' => 'nullable|string',
            'field_settings' => 'nullable|array',
        ]);

        $setting = $form->setting ?? new FormSetting(['form_id' => $form->id]);
        $setting->fill($settings);
        $setting->save();

        return response()->json(['success' => true]);
    }

// به روز رسانی ترتیب فیلدها و کلاس ستون
    public function updateFieldsDisplay(Request $request, Form $form)
    {
        $fieldsOrder = $request->order;

        foreach ($fieldsOrder as $index => $fieldData) {
            $field = FormField::find($fieldData['id']);
            if ($field && $field->form_id == $form->id) {
                $field->update([
                    'order' => $index,
                    'column_class' => $fieldData['column_class'] ?? $field->column_class,
                    'show_label' => $fieldData['show_label'] ?? $field->show_label,
                ]);
            }
        }

        return response()->json(['success' => true]);
    }
}
