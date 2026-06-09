@if($links->where('link_group_id', $group['key'])->count())
    <section class="card links-sortable">
        <div class="card-header">
            <h4 class="card-title">{{ option('link_groups_' . $group['key'], $group['name']) }}</h4>
        </div>
        <div class="card-content">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th class="text-center">ردیف</th>
                                <th>عنوان</th>
                                <th>لینک</th>
                                <th class="text-center" style='width: 150px'>عملیات</th>
                            </tr>
                        </thead>
                        <tbody id="links-sortable-{{ $loop->index }}">
                            @foreach($links->where('link_group_id', $group['key']) as $link)
                                <tr id="link-{{ $link->id }}">
                                    <td class="text-center draggable-handler">
                                        <div class="fonticon-wrap"><i class="feather icon-move"></i></div>
                                    </td>

                                    <td>{{ $link->title }}</td>
                                    <td>{{ $link->link }}</td>

                                    <td class="text-center">
                                        <div class="dropdown dropdown-action">
                                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu{{ $link->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenu{{ $link->id }}">
                                                @can('links.update')
                                                    <a class="dropdown-item" href="{{ route('admin.links.edit', ['link' => $link]) }}"><i class="fa-solid fa-pencil mr-1"></i>ویرایش</a>
                                                    <div class="dropdown-divider"></div>
                                                @endcan
                                                @can('links.delete')
                                                    <button class="dropdown-item btn-delete" data-link="{{ $link->id }}"  data-toggle="modal" data-target="#delete-modal"><i class="fa-solid fa-trash-can mr-1"></i> حذف</button>
                                                @endcan
                                            </div>
                                        </div>

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

@else
    <section class="card links-sortable">
        <div class="card-header">
            <h4 class="card-title">{{ $group['name'] }}</h4>
        </div>
        <div class="card-content">
            <div class="card-body">
                <div class="card-text">
                    <p>چیزی برای نمایش وجود ندارد!</p>
                </div>
            </div>
        </div>
    </section>
@endif
