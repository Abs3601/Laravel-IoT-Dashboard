@props(['event', 'prev', 'device'])

@php
    $msgs = [];
    // State change
    if (!$prev || $prev->state !== $event->state) {
        $msgs[] = strtolower($event->state) == 'on' ? 'turned on' : (strtolower($event->state) == 'off' ? 'turned off' : "changed to {$event->state}");
    }
    
    // Brightness change
    if ($prev && isset($event->attributes['brightness']) && ($prev->attributes['brightness'] ?? 0) != $event->attributes['brightness']) {
        $msgs[] = 'brightness changed';
    }
    
    // Color change
    if ($prev && (isset($event->attributes['rgb_color']) || isset($event->attributes['rgb']) || isset($event->attributes['color']))) {
        $currColor = $event->attributes['rgb_color'] ?? $event->attributes['rgb'] ?? $event->attributes['color'];
        $prevColor = $prev->attributes['rgb_color'] ?? $prev->attributes['rgb'] ?? $prev->attributes['color'] ?? [];
        
        if ($currColor != $prevColor) {
            $rgbStr = is_array($currColor) ? "rgb(".implode(',', array_slice($currColor, 0, 3)).")" : $currColor;
            $msgs[] = "colour changed <span class='colour-change-circle inline-block w-3 h-3 rounded-full ml-1 border border-white/20 cursor-pointer hover:scale-110 active:scale-90 transition-transform relative align-middle' style='background-color: {$rgbStr}' data-colour='{$rgbStr}'></span>";
        }
    }
    
    $description = count($msgs) > 0 ? implode(' and ', $msgs) : 'updated';
@endphp

<div class="px-6 py-4 hover:bg-white/5 flex items-center justify-between gap-4 group transition-colors">
    <div class="flex-grow">
        <p class="text-sm text-white">
            <span class="font-bold text-blue-400">{{ $device->friendly_name ?? $device->entity_id }}</span> 
            <span class="text-gray-300 font-medium">{!! $description !!}</span>
        </p>
    </div>
    
    <div class="text-right flex-shrink-0">
        <p class="text-[11px] text-gray-400 font-medium">{{ $event->created_at->format('H:i') }}</p>
        <p class="text-[9px] text-gray-500 uppercase tracking-tight">{{ $event->created_at->diffForHumans() }}</p>
    </div>
</div>
