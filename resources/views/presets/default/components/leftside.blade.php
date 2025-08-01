<!-- left side -->
<div class="col-lg-3">
    <aside class="leftside-bar">
        <!-- menu-item-wraper -->
        <div class="menu-item-wraper">
            <div class="nav-menu">
                <div class="navmenu-item-wraper">
                    <a href="{{ route('home') }}" class="menu-item">
                        <i class="fa-solid fa-house"></i>
                        <h6 class="menu-name {{ menuActive('home') }}">@lang('Home')</h6>
                    </a>
                    <a href="{{ route('post.popular') }}" class="menu-item">
                        <i class="fa-solid fa-star"></i>
                        <h6 class="menu-name {{ menuActive('post.popular') }}">@lang('Popular')</h6>
                    </a>
                    {{-- <a href="{{ route('post.job') }}" class="menu-item">
                        <i class="fa-solid fa-toolbox"></i>
                        <h6 class="menu-name {{ menuActive('post.job') }}">@lang('Jobs')</h6>
                    </a> --}}

                    {{-- <a href="{{ route('post.events') }}" class="menu-item">
                        <i class="fa-solid fa-calendar-days"></i>
                        <h6 class="menu-name {{ menuActive('post.events') }}">@lang('Events')</h6>
                    </a> --}}

                    <a href="{{ route('save.post') }}" class="menu-item">
                        <i class="fa-solid fa-bookmark"></i>
                        <h6 class="menu-name {{ menuActive('save.post') }}">@lang('Bookmarks')</h6>
                    </a>

                </div>
            </div>
            <!-- menu-item-wraper / -->
            <!-- latest-topics-menu -->
            <div class="latest-topics-menu">
                <div class="latest-topics-wraper">
                    <h6 class="menu-title">@lang('TOPICS')</h6>
                    <div class="latest-topics-list">
                        @foreach ($categories as $category)
                            @if ($loop->iteration > 0 && $loop->iteration <= 4)
                                <a href="{{ route('post.category', [slug($category->name), $category->id]) }}"
                                    class="menu-item">
                                    @php echo  $category->icon ; @endphp
                                    <h6
                                        class="menu-name {{ url()->current() == route('post.category', [slug($category->name), $category->id]) ? 'active' : '' }}">
                                        {{ $category->name }}</h6>
                                </a>
                            @endif
                            @if ($loop->iteration > 4)
                                <div class="show-all-menu-wraper">
                                    <div class="show-all-menu-item">
                                        <a href="{{ route('post.category', [slug($category->name), $category->id]) }}"
                                            class="menu-item">
                                            @php echo  $category->icon ; @endphp
                                            <h6 class="menu-name">{{ $category->name }}</h6>
                                        </a>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                        <div class="menu-item">
                            <button class="show-all-tgl-btn">@lang('See More')</button>
                        </div>

                    </div>
                </div>
            </div>
            <!-- latest-topics-menu / -->

            <!-- others-menu -->
            <div class="others-menu">
                <div class="others-item-wraper">
                    <h6 class="menu-title">@lang('Others')</h6>

                    <div class="others-item-list">
                        @if (@$cookie_policy->data_values?->status == 1)
                            <a href="{{ route('cookie.policy') }}" class="menu-item">
                                @php echo  @$cookie_policy->data_values?->cookie_icon ; @endphp
                                <h6 class="menu-name {{ menuActive('cookie.policy') }}">@lang('Cookie') </h6>
                            </a>
                        @endif
                    </div>
                    @if (@$general->agree == 1)
                        @foreach ($policy_elements as $element)
                            <a href="{{ route('policy.pages', [slug($element->data_values?->title), $element->id]) }}"
                                class="menu-item">
                                @php echo  @$element->data_values?->policy_icon ; @endphp
                                <h6
                                    class="menu-name {{ url()->current() == route('policy.pages', [slug($element->data_values?->title), $element->id]) ? 'active' : '' }}">
                                    {{ $element->data_values?->title }}</h6>
                            </a>
                        @endforeach
                    @endif
                </div>
            </div>
            <!-- others-menu /-->
          
            <div class="copy-right-text text-center ps-5 mt-4">
                <p class="bottom-footer-text"> &copy; @lang('Copyright') {{ now()->year }} @lang('. All rights reserved.')</p>
            </div>
        </div>
    </aside>
</div>
<!-- left side / -->
