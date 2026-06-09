<div class="row">

    <div class="col-md-6">
        <div class="form-group">
            <label>نام دسته بندی </label>
            <input type="text" name="title" class="form-control" value="{{ $category->title }}">
        </div>
    </div>
    <div class="col-md-6">
        <fieldset class="form-group">
            <label>تصویر</label>
            <div id="image" class="custom-file">
                <input  type="file" accept="image/*" name="image" class="custom-file-input">
                <label class="custom-file-label" for="image">{{ $category->image }}</label>
                <small>بهترین اندازه <span class="text-danger">{{ config('front.imageSizes.CategoryImage') }}</span> پیکسل می باشد.</small>
            </div>
        </fieldset>
    </div>
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-6">
                <fieldset class="form-group">
                    <label>نوع فیلتر</label>
                    <select id="filter_type" name="filter_type" class="form-control">
                        <option value="inherit" {{ $category->filter_type == 'inherit' ? 'selected' : '' }}>ارث بری از دسته بالاتر</option>
                        <option value="none" {{ $category->filter_type == 'none' ? 'selected' : '' }}>بدون فیلتر</option>
                        <option value="filterId" {{ $category->filter_type == 'filterId' ? 'selected' : '' }}>انتخاب فیلتر</option>
                    </select>
                </fieldset>
            </div>
            <div class="col-md-6">
                <fieldset class="form-group">
                    <label>انتخاب فیلتر</label>
                    <select id="filter_id" name="filter_id" class="form-control">
                        @foreach ($filters as $filter)
                            <option value="{{ $filter->id }}" {{ $category->filter_id == $filter->id ? 'selected' : '' }}>{{ $filter->title }}</option>
                        @endforeach
                    </select>
                </fieldset>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <fieldset class="form-group">
            <label>تصویر پس زمینه</label>
            <div id="background_image" class="custom-file">
                <input  type="file" accept="image/*" name="background_image" class="custom-file-input">
                <label class="custom-file-label" for="background_image">{{ $category->background_image }}</label>
            </div>
        </fieldset>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>عنوان سئو </label>
            <input type="text" name="meta_title" class="form-control" value="{{ $category->meta_title }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>url</label>
            <input id="slug" type="text" class="form-control" name="slug" value="{{ $category->slug }}">
            <p>
                <small >
                    <a id="generate-category-slug" >ایجاد خودکار</a>
                    <span id="slug-spinner" class="spinner-grow spinner-grow-sm text-success" role="status" style="display: none;">
                        <span class="sr-only">Loading...</span>
                    </span>
                </small>
            </p>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>توضیحات سئو</label>
            <textarea class="form-control" name="meta_description" rows="3">{{ $category->meta_description }}</textarea>
        </div>
    </div>
    <div class="col-md-6">
        <fieldset class="form-group">
            <label>کلمات کلیدی</label>
            <input type="text" name="tags" class="form-control tags" value="{{ $category->getTags }}">
        </fieldset>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>میزان کمیسیون(درصد)
                <button type="button" class="btn-help" data-toggle="modal" data-target="#commissionGuideModal" style="background: none; border: none; cursor: pointer; font-size: 18px;">
                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; background: #17a2b8; color: white; border-radius: 50%; font-weight: bold;">?</span>
                </button>
            </label>
            <input type="number" name="commission" class="form-control" value="{{ $category->commission }}">
        </div>

    </div>
    <div class="col-md-6">
        <fieldset class="checkbox form-group">
            <div class="vs-checkbox-con vs-checkbox-primary" style="    margin-top: 37px;">
                <input type="checkbox" name="published" {{ $category->published ? 'checked' : '' }}>
                <span class="vs-checkbox">
                    <span class="vs-checkbox--check">
                        <i class="vs-icon feather icon-check"></i>
                    </span>
                </span>
                <span>انتشار دسته بندی؟</span>
            </div>
        </fieldset>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <label>توضیحات </label>
            <textarea id="category-description" class="form-control" name="description" rows="3">{{ $category->description }}</textarea>
        </div>
    </div>
</div>

<!-- مودال -->
<div class="modal fade" id="commissionGuideModal" tabindex="-1" role="dialog" aria-labelledby="commissionGuideModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title" id="commissionGuideModalLabel">
                    📘 راهنمای تنظیم کمیسیون دسته‌بندی‌ها
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <div style="margin-bottom: 20px; padding: 15px; background: #f0f8ff; border-radius: 10px; border-right: 4px solid #17a2b8;">
                    <strong style="color: #17a2b8;">🎯 نحوه عملکرد:</strong>
                </div>

                <div style="margin-bottom: 20px;">
                    <p style="margin-bottom: 10px;"><strong style="color: #28a745;">✓</strong> اگر برای یک دسته‌بندی، مقدار کمیسیون وارد کنید <strong>(حتی عدد صفر)</strong>:</p>
                    <p style="margin-right: 25px; color: #555;">→ همان مقدار برای آن دسته و محصولات داخل آن <strong>قطعی و نهایی</strong> است<br>
                        → سیستم <strong>به هیچ والد بالاتری</strong> مراجعه نمی‌کند</p>
                </div>

                <div style="margin-bottom: 20px;">
                    <p style="margin-bottom: 10px;"><strong style="color: #ffc107;">⚠</strong> اگر فیلد کمیسیون را <strong>خالی بگذارید (null)</strong>:</p>
                    <p style="margin-right: 25px; color: #555;">→ سیستم به صورت خودکار سراغ <strong>دسته‌بندی والد (parent)</strong> می‌رود<br>
                        → تا اولین دسته‌بندی که کمیسیون دارد را پیدا کند<br>
                        → اگر به ریشه برسد و هیچ کمیسیونی پیدا نشود → کمیسیون <strong>صفر</strong> اعمال می‌شود</p>
                </div>

                <div style="margin: 20px 0;">
                    <table style="width: 100%; border-collapse: collapse; text-align: right; direction: rtl;">
                        <thead>
                        <tr><th style="border: 1px solid #ddd; padding: 8px; background: #f2f2f2;">دسته‌بندی</th>
                            <th style="border: 1px solid #ddd; padding: 8px; background: #f2f2f2;">کمیسیون ثبت‌شده</th>
                            <th style="border: 1px solid #ddd; padding: 8px; background: #f2f2f2;">نتیجه نهایی</th>
                            <th style="border: 1px solid #ddd; padding: 8px; background: #f2f2f2;">توضیح</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr><td style="border: 1px solid #ddd; padding: 8px;">لوازم خانگی (والد)</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">۱۰٪</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">۱۰٪</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">-</td>
                        </tr>
                        <tr><td style="border: 1px solid #ddd; padding: 8px;">└ یخچال (فرزند)</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">خالی</td>
                            <td style="border: 1px solid #ddd; padding: 8px; background: #fff3cd;">۱۰٪</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">از والد ارث می‌برد</td>
                        </tr>
                        <tr><td style="border: 1px solid #ddd; padding: 8px;">└ ماشین لباسشویی</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">۰٪</td>
                            <td style="border: 1px solid #ddd; padding: 8px; background: #d4edda;">۰٪</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">صفر عمدی، به والد نگاه نمی‌کند</td>
                        </tr>
                        <tr><td style="border: 1px solid #ddd; padding: 8px;">└ جاروبرقی</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">۱۵٪</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">۱۵٪</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">مقدار خاص خودش را دارد</td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div style="margin: 20px 0; padding: 15px; background: #fff3cd; border-right: 4px solid #ffc107; border-radius: 5px;">
                    <strong style="color: #856404;">⚠️ نکات مهم:</strong>
                    <ul style="margin-top: 10px; margin-right: 20px;">
                        <li><strong>عدد صفر (0) با خالی بودن (null) تفاوت دارد</strong>
                            <ul>
                                <li><code>0</code> = "از این دسته کمیسیون نگیر" (عمدی)</li>
                                <li><code>null</code> = "مقدار را از والد به ارث ببر"</li>
                            </ul>
                        </li>
                        <li style="margin-top: 10px;">برای <strong>عدم دریافت کمیسیون</strong> از یک دسته خاص، حتماً <strong>عدد صفر</strong> را وارد کنید</li>
                        <li style="margin-top: 10px;">برای <strong>ارث‌بری از والد</strong>، فیلد را <strong>خالی</strong> بگذارید</li>
                    </ul>
                </div>

                <div style="margin: 20px 0; padding: 15px; background: #d1ecf1; border-right: 4px solid #17a2b8; border-radius: 5px;">
                    <strong style="color: #0c5460;">✅ پیشنهاد:</strong>
                    <p style="margin-top: 10px; margin-right: 10px;">در سطح <strong>ریشه دسته‌بندی‌ها</strong> حتماً یک مقدار پایه (مثلاً ۵ یا ۱۰ درصد) تعریف کنید تا در صورت خالی بودن فرزندان، سیستم مقدار پیش‌فرض داشته باشد.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">متوجه شدم</button>
            </div>
        </div>
    </div>
</div>
