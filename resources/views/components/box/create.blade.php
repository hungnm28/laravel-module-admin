@props(['listing'=>'','btns'=>null])
<div class="w-full bg-white rounded overflow-hidden text-sm shadow">
    <div class="w-full min-h-12   border-teal-500  border-t-4">
        <div class="w-full flex border-b items-center justify-between">
            <span class="flex-none text-gray-800  text-xl p-2">Create</span>
            <div class="flex-none p-2 grid gap-1">
                @if($listing)
                    <a class="btn-primary" href="{{$listing}}">{!! lmaIcon('list') !!}<span class="text">List</span></a>
                @endif
            </div>
        </div>
    </div>
    <div class="w-full flex p-2 pt-5">
        <div class="flex-none w-full max-w-screen-lg">{{$slot}}</div>
    </div>
    <div class="w-full flex border-t p-2">
        <div class="w-full max-w-screen-lg flex-none flex items-center lg:pl-[15%]">
            <label class="btn-primary mr-2" wire:click="store">Create</label>
            <div class="flex-none grid grid-flow-col gap-4 text-xs">
                <label class=" flex  items-center">
                    <input type="radio" wire:model="done" value="0">
                    <span class="ml-1">Continue Create</span>
                </label>
                <label class="flex items-center">
                    <input type="radio" wire:model="done" value="1">
                    <span class="ml-1">View</span>
                </label>
                <label class=" flex items-center">
                    <input type="radio" wire:model="done" value="2">
                    <span class="ml-1">Edit</span>
                </label>
                <label class=" flex items-center">
                    <input type="radio" wire:model="done" value="3">
                    <span class="ml-1">Listing</span>
                </label>
            </div>
            <div class="flex-auto "></div>
            <label class="btn ml-2" wire:click="resetForm">Reset</label>
        </div>
    </div>
</div>
