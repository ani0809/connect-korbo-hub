@php($icons=$settings["show"] ?? ["facebook","instagram","twitter"])<div class="el-social">@foreach($icons as $i)<a href="#" target="_blank">{{ ucfirst($i) }}</a>@endforeach</div>
