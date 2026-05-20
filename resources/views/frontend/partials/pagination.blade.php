@if ($paginator->hasPages())
<div class="mt-6 flex justify-center">
    {{ $paginator->withQueryString()->links() }}
</div>
@endif
