

<div id="sidebar" class="sidebar responsive">
	<ul class="nav nav-list">
        <li>
            <a href="{{url('/')}}">
                <i class="menu-icon fa fa-home"></i>
                <span class="menu-text">{{ trans('sidebar.home') }}</span>
            </a>
            <b class="arrow"></b>
        </li>
	    @foreach($SideBar->whereParent() as $item)
	   		<li>
	        	@if ($item->link)
	                <a @if($item->hasChildren()) class="dropdown-toggle" @endif href="{!! $item->url() !!}">
	                    <i class="menu-icon {!! $item->data['icon'] !!}"></i>
	                    <span class="menu-text">{{ $item->title }}</span>
	                    @if ($item->hasChildren())
	                        <b class="arrow fa fa-angle-down"></b>
	                    @endif
	                </a>
	                <b class="arrow"></b>
	            @else
	                <span>{{ $item->title }}</span>
	            @endif
	            @if ($item->hasChildren())
	                <ul class="submenu">
	                    @foreach($item->children() as $subItem)
	                        @if ($subItem->data['show'])
	                            <li>
	                                <a href="{!! $subItem->url() !!}">
	                                    <i class="menu-icon fa"></i>
	                                    {{ $subItem->title }}
	                                </a>
	                                <b class="arrow"></b>
	                            </li>
	                        @endif
	                    @endforeach
	                </ul>
	            @endif
	        </li>
	    @endforeach
    </ul>
</div>