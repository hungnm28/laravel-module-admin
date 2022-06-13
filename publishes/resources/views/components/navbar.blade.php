<ul id="menus">
    @if(Route::has(config('lma.module.route')))
        <li class="item {{ request()->routeIs(config('lma.module.route')) ? 'active' : '' }}">
            <a href="{{route(config('lma.module.route'))}}" class="link"><span class="icon">{!! lmaIcon("home") !!}</span><span class="link-title">Home Admin</span></a>
        </li>
    @endif
    @foreach(data_get($data,"0",[]) as $item)
        @if(Route::has($item->route) )
            <li class="item {{ request()->routeIs($item->route.'*') ? 'active' : '' }} ">
                <a href="{{route($item->route)}}" class="link"><span class="icon">{!! lmaIcon($item->icon) !!}</span><span class="link-title">{{$item->name}}</span></a>
                @if(data_get($data,$item->id))
                    <ul class="children">
                        @foreach(data_get($data,$item->id,[]) as $child)
                            @if(Route::has($child->route))
                                <li class="child {{ request()->routeIs($child->route.'*') ? 'active' : '' }} ">
                                    <a class="link" href="{{route($child->route)}}">
                                        <span class="icon">{!! lmaIcon($child->icon) !!}</span><span class="link-title">{{$child->name}}</span>
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                @endif
            </li>
        @endif
    @endforeach
</ul>
