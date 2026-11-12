<?php
// includes/footer.php
?>
    <!-- Flash Toast if set -->
    <?php $flash = get_flash(); if ($flash): ?>
    <div id="toastMessage" class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-4 py-3 rounded-2xl shadow-xl border text-sm font-medium transition-all duration-300 transform translate-y-0
        <?= $flash['type'] === 'success' ? 'bg-emerald-900 text-white border-emerald-700' : 
           ($flash['type'] === 'error' ? 'bg-rose-900 text-white border-rose-700' : 'bg-slate-900 text-white border-slate-700') ?>">
        <span><?= htmlspecialchars($flash['message']) ?></span>
        <button onclick="document.getElementById('toastMessage').remove()" class="text-white/60 hover:text-white p-1">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    </div>
    <script>
        setTimeout(() => {
            const toast = document.getElementById('toastMessage');
            if (toast) {
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => toast.remove(), 300);
            }
        }, 4000);
    </script>
    <?php endif; ?>
</body>
</html>

