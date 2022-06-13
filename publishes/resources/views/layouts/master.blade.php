<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{isset($title)?$title:"admin"}}</title>
    <!-- Styles -->
    <link rel="stylesheet" href="{{ mix('assets/css/admin.css') }}">
    <!-- Scripts -->
    <script src="{{ mix('assets/js/admin.js') }}" defer></script>
    @livewireStyles
</head>
<body class="w-full min-h-screen bg-gray-200 text-base max-h-screen" style="font-size: 12px">
<input type="checkbox" id="page-control">
<div id="adm-container" class="w-full h-screen overflow-y-auto block">
    <div class="w-full h-12 bg-green-600 flex items-center">
        <label for="page-control" class="w-12 flex items-center justify-center border-green-700 border-r h-full text-white hover:bg-green-700 cursor-pointer">{!! lmaIcon('menu',24) !!}</label>
        <div class="flex-auto"></div>
        <div class="group w-12 flex-none border-green-700 border-l h-full relative">
            <label class="w-full h-12 flex items-center justify-center text-white cursor-pointer hover:bg-green-700 group-hover:bg-green-700">{!! lmaIcon('person',24) !!}</label>
            <div class="w-64 absolute right-0 top-12 bg-white rounded-b shadow overflow-hidden hidden z-50 group-hover:block">
                <div class="w-full h-32 bg-gray-500 flex flex-col items-center justify-center">
                    <div class="w-16 h-16 flex-none rounded-full bg-gray-300 flex items-center justify-center text-gray-700">
                        {!! lmaIcon('person',56) !!}
                    </div>
                    <span class="w-full text-center p-2">{{Auth::user()->name}}</span>
                </div>
                <div class="w-full bg-gray-100 flex p-2 justify-between">
                    <a class="btn">Setting</a>
                    <form method="post" action="{{route('logout')}}">
                        @csrf
                        <button class="btn">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="w-full flex min-h-screen ">
        <div id="adm-menu">
               <x-admin::navbar />
        </div>
        <div class="flex-1 p-4 overflow-hidden">
            {{$slot}}

        </div>
    </div>

</div>
@livewireScripts
@stack('scripts')
</body>
</html>
