@props(["name","params"=>[],"label"=>null,'mode'=>'.debounce.600ms',"class"=>"",'type'=>'text','placeholder'=>null, 'suggest'=>[]])
@php
    $placeholder = ($placeholder!=''?$placeholder:Illuminate\Support\Str::headline($name)).'...';

@endphp
<x-lma.form.field :name="$name" :label="$label">
    <div class="w-full flex items-start flex-wrap">
        @foreach($params as $k=>$value)
            <span class="flex-none flex items-center m-1 bg-green-100 text-xs border-green-700 border">
                <span class="flex-1 px-1">{{$value}}</span>
                <span class="flex-none cursor-pointer" wire:click="removeItem('{{$name}}',{{$k}})">{!! lmaIcon("close") !!}</span>
            </span>
        @endforeach
    </div>
    <div class="w-full flex my-2" x-data="{ param: '', addItem() {
            $wire.addItem('{{$name}}',this.param);
            this.param='';
        } }">
        <div x-data="{ open: false }" class="w-full flex-1 relative" @click.outside="open = false">
            <input @focus="open = !open" x-model="param" type="{{$type}}" @keyup.enter="addItem()" placeholder="{{$placeholder}}" {{$attributes}} class="form-input"/>
            @if($suggest)
                <div x-show="open" class="w-full block left-0 right-0 mt-0 bg-white border shadow rounded-b" style="">
                    @foreach($suggest as $sg)
                        <label @click="open = false" class="w-full block border-b p-2 cursor-pointer" wire:click="addItem('{{$name}}','{{$sg}}')">{{$sg}}</label>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="flex-none">
            <label class="flex-none rounded cursor-pointer w-10 py-3 ml-1 flex items-center justify-center border bg-green-600 text-white hover:bg-green-700" @click="addItem()">{!! lmaIcon("add-circle",12) !!}</label>
        </div>
    </div>
</x-lma.form.field>
