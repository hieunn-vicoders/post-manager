@php
    if(!isset($top_level) || !is_int($top_level)) $top_level = 1;   
    if(!isset($depth) || !is_int($depth)) $depth = 6;
    if (!isset($custom_class)) $custom_class = 'default_table_of_contents';
@endphp
<div class="{{$custom_class}} show" id="toc">
    <div class="default_tablde_of_contents_header">
        <div class="title">Mục lục</div>
        <div class="toggle" id="toc_toggle"><img src="/images/icon/toc_dropdown.svg" alt="icontoc_dropdown.svg"></div>
    </div>
    {!! $post->getTableOfContents($top_level, $depth) !!}
</div>

<script>
    document.getElementById("toc_toggle").addEventListener("click", function(){ 
        document.getElementById("toc").classList.toggle("show");
    });
</script>