@extends('back.layouts.master')

@section('content')

    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <div class="breadcrumb-wrapper col-12">
                                <ol class="breadcrumb no-border">
                                    <li class="breadcrumb-item">مدیریت
                                    </li>
                                    <li class="breadcrumb-item">مدیریت کاربران
                                    </li>
                                    <li class="breadcrumb-item active">ویرایش کاربر
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="content-body">
                <!-- Description -->
                <section id="main-card" class="card">
                    <div class="card-header">
                        <h4 class="card-title">ویرایش کاربر </h4>
                    </div>

                    <div id="main-card" class="card-content">
                        <div class="card-body">
                            <div class="col-12 col-md-10 offset-md-1">
                                <form class="form" id="user-edit-form"
                                    action="{{ route('admin.users.update', ['user' => $user]) }}" method="post">
                                    @csrf
                                    @method('put')
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label>نام</label>
                                                    <input type="text" class="form-control" name="first_name"
                                                        value="{{ $user->first_name }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label>نام خانوادگی</label>
                                                    <input type="text" class="form-control" name="last_name"
                                                        value="{{ $user->last_name }}">
                                                </div>
                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label>آدرس ایمیل</label>
                                                    <input type="email" class="form-control" name="email"
                                                        value="{{ $user->email }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label>شماره همراه <small>( نام کاربری )</small></label>
                                                    <input type="text" class="form-control ltr" name="username"
                                                        value="{{ $user->username }}">
                                                </div>
                                            </div>

                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label>کد ملی</label>
                                                    <input type="text" class="form-control ltr" name="national_code"
                                                        value="{{ $user->national_code }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-12">
                                                <?php $birth_date = explode('/', $user->birth_date); ?>

                                                <label>تاریخ تولد</label>
                                                <div class=" row">
                                                    <div class="form-group col-md-4 col-12">
                                                        <select class="form-control" name="day" id="day">
                                                            <option value="date-desc" selected="selected">روز</option>
                                                            @for ($i = 1; $i <= 31; $i++)
                                                                <option @if (@$birth_date[2] == $i) selected @endif
                                                                    value="{{ $i }}">{{ $i }}</option>
                                                            @endfor
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-4 col-12">
                                                        <select class="form-control" name="month" id="month">
                                                            <option value="date-desc" selected="selected">ماه</option>
                                                            <option @if (@$birth_date[1] == 1) selected @endif
                                                                value="1">فروردین</option>
                                                            <option @if (@$birth_date[1] == 2) selected @endif
                                                                value="2">اردیبهشت</option>
                                                            <option @if (@$birth_date[1] == 3) selected @endif
                                                                value="3">خرداد</option>
                                                            <option @if (@$birth_date[1] == 4) selected @endif
                                                                value="4">تیر</option>
                                                            <option @if (@$birth_date[1] == 5) selected @endif
                                                                value="5">مرداد</option>
                                                            <option @if (@$birth_date[1] == 6) selected @endif
                                                                value="6">شهریور</option>
                                                            <option @if (@$birth_date[1] == 7) selected @endif
                                                                value="7">مهر</option>
                                                            <option @if (@$birth_date[1] == 8) selected @endif
                                                                value="8">آبان</option>
                                                            <option @if (@$birth_date[1] == 9) selected @endif
                                                                value="9">آذر</option>
                                                            <option @if (@$birth_date[1] == 10) selected @endif
                                                                value="10">دی</option>
                                                            <option @if (@$birth_date[1] == 11) selected @endif
                                                                value="11">بهمن</option>
                                                            <option @if (@$birth_date[1] == 12) selected @endif
                                                                value="12">اسفند</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-4 col-12">
                                                        <select class="form-control" name="year" id="year">
                                                            <option value="date-desc" selected="selected">سال</option>
                                                            @for ($i = 1360; $i <= 1400; $i++)
                                                                <option @if (@$birth_date[0] == $i) selected @endif
                                                                    value="{{ $i }}">{{ $i }}</option>
                                                            @endfor
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>


                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label>شماره کارت</label>
                                                    <input type="text" class="form-control ltr" name="card_number"
                                                        value="{{ $user->card_number }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <fieldset class="form-group">
                                                    <label>تصویر</label>
                                                    <div class="custom-file">
                                                        <input id="image" type="file" accept="image/*"
                                                            name="image" class="custom-file-input">
                                                        <label class="custom-file-label" for="image"></label>
                                                    </div>
                                                </fieldset>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label>گذرواژه</label>
                                                    <input type="password" id="password" class="form-control"
                                                        name="password">
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label>تکرار گذرواژه</label>
                                                    <input type="password" class="form-control ltr"
                                                        name="password_confirmation">
                                                </div>
                                            </div>



                                        </div>

                                        @if (count($filds))
                                        <div class="row">
                                            @foreach ($filds as $fild)
                                                <div class="col-md-6 col-12">
                                                    <div class="form-group">
                                                        <label>{{ $fild->title }}</label>

                                                        @php
                                                            // بررسی فیلد اجباری بودن
                                                            $isRequired = $fild->required ? 'required' : '';

                                                            // پیدا کردن مقدار مربوط به این فیلد
                                                            $fieldValue = $fieldValues->firstWhere('field_id', $fild->id);
                                                        @endphp

                                                        @if ($fild->type == 'input')
                                                            <input type="text" class="form-control" name="filds[{{ $fild->id }}]"
                                                                   value="{{ old('filds.'.$fild->id, $fieldValue ? $fieldValue->value : '') }}" {{ $isRequired }}>

                                                        @elseif ($fild->type == 'textarea')
                                                            <textarea class="form-control" name="filds[{{ $fild->id }}]" {{ $isRequired }}>{{ old('filds.'.$fild->id, $fieldValue ? $fieldValue->value : '') }}</textarea>

                                                        @elseif ($fild->type == 'number')
                                                            <input type="number" class="form-control" name="filds[{{ $fild->id }}]"
                                                                   value="{{ old('filds.'.$fild->id, $fieldValue ? $fieldValue->value : '') }}" {{ $isRequired }}>

                                                        @elseif ($fild->type == 'email')
                                                            <input type="email" class="form-control" name="filds[{{ $fild->id }}]"
                                                                   value="{{ old('filds.'.$fild->id, $fieldValue ? $fieldValue->value : '') }}" {{ $isRequired }}>

                                                        @elseif ($fild->type == 'colorPicker')
                                                            <input type="color" class="form-control" name="filds[{{ $fild->id }}]"
                                                                   value="{{ old('filds.'.$fild->id, $fieldValue ? $fieldValue->value : '') }}" {{ $isRequired }}>

                                                        @elseif ($fild->type == 'checkbox')
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="filds[{{ $fild->id }}]"
                                                                       value="1" {{ $fieldValue && $fieldValue->value == 1 ? 'checked' : '' }} {{ $isRequired }}>
                                                                <label class="form-check-label">{{ $fild->title }}</label>
                                                            </div>

                                                        @elseif ($fild->type == 'select')
                                                            @php
                                                                $options = explode(',', $fild->select_options); // تبدیل رشته به آرایه
                                                            @endphp
                                                            <select class="form-control" name="filds[{{ $fild->id }}]" {{ $isRequired }}>
                                                                @foreach ($options as $option)
                                                                    <option value="{{ trim($option) }}"
                                                                            {{ trim($option) == ($fieldValue ? $fieldValue->value : '') ? 'selected' : '' }}>
                                                                        {{ trim($option) }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        @endif

                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif



                                        <div class="row">
                                            <div class="col-md-3 mb-2">
                                                <fieldset class="checkbox">
                                                    <div class="vs-checkbox-con vs-checkbox-primary">
                                                        <input type="checkbox" name="verified_at"
                                                            {{ $user->verified_at ? 'checked' : '' }}>
                                                        <span class="vs-checkbox">
                                                            <span class="vs-checkbox--check">
                                                                <i class="vs-icon feather icon-check"></i>
                                                            </span>
                                                        </span>
                                                        <span>شماره تلفن تایید شده</span>
                                                    </div>
                                                </fieldset>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-12">
                                                <button type="submit"
                                                    class="btn btn-primary mr-1 mb-1 waves-effect waves-light">ویرایش
                                                    کاربر</button>
                                            </div>
                                            <div class="col-12">
                                                <div class="alert alert-info mt-1 alert-validation-msg" role="alert">
                                                    <i class="feather icon-info ml-1 align-middle"></i>
                                                    <span>در صورتی که نمیخواهید گذرواژه را عوض کنید، فیلدهای گذرواژه را خالی
                                                        بگذارید.</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>
                <!--/ Description -->

            </div>
        </div>
    </div>

@endsection

@include('back.partials.plugins', ['plugins' => ['jquery.validate']])

@php
    $help_videos = [config('general.video-helpes.users')];
@endphp

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/users/all.js') }}"></script>
    <script src="{{ asset('back/assets/js/pages/users/create.js') }}"></script>
@endpush
