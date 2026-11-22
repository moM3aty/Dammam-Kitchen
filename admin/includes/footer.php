<?php if (!defined('SK_ADMIN')) { exit; } ?>
</div><!-- /.content -->
</main>
</div><!-- /.admin-body -->

<script>
/* ═══════════════════════════════════════════
   SKSwal — SweetAlert2 Helper
   ═══════════════════════════════════════════ */
window.SKSwal = (function(){
    if (typeof Swal === 'undefined') {
        return {
            confirm: function(opts){ return Promise.resolve({ isConfirmed: window.confirm(opts.text || opts.title || 'تأكيد؟') }); },
            toast: function(){}, success: function(){}, error: function(){}, info: function(){}, warn: function(){}
        };
    }

    var baseTheme = {
        customClass: {
            popup: 'sk-swal-popup',
            title: 'sk-swal-title',
            htmlContainer: 'sk-swal-html',
            confirmButton: 'sk-swal-confirm',
            cancelButton: 'sk-swal-cancel',
            actions: 'sk-swal-actions',
            icon: 'sk-swal-icon'
        },
        buttonsStyling: false,
        reverseButtons: true,
        focusConfirm: false
    };

    function merge(o, t){
        var r = {};
        for (var k in o) r[k] = o[k];
        for (var k in t) {
            if (k === 'customClass') {
                r.customClass = Object.assign({}, o.customClass || {}, t.customClass || {});
            } else {
                r[k] = t[k];
            }
        }
        return r;
    }

    return {
        confirm: function(opts){
            return Swal.fire(merge(baseTheme, Object.assign({
                title: 'تأكيد',
                text: 'هل أنت متأكد؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'نعم، متأكد',
                cancelButtonText: 'إلغاء',
                confirmButtonClass: 'sk-swal-confirm'
            }, opts)));
        },
        danger: function(opts){
            return Swal.fire(merge(baseTheme, Object.assign({
                title: 'تأكيد الحذف',
                text: 'هل أنت متأكد من الحذف؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'نعم، احذف',
                cancelButtonText: 'إلغاء',
                customClass: Object.assign({}, baseTheme.customClass, { confirmButton: 'sk-swal-confirm sk-swal-confirm-danger' })
            }, opts)));
        },
        success: function(title, text){
            return Swal.fire(merge(baseTheme, {
                title: title || 'نجاح',
                text: text || '',
                icon: 'success',
                confirmButtonText: 'حسناً',
                timer: 3000,
                timerProgressBar: true
            }));
        },
        error: function(title, text){
            return Swal.fire(merge(baseTheme, {
                title: title || 'خطأ',
                text: text || '',
                icon: 'error',
                confirmButtonText: 'حسناً'
            }));
        },
        info: function(title, text){
            return Swal.fire(merge(baseTheme, {
                title: title || 'معلومة',
                text: text || '',
                icon: 'info',
                confirmButtonText: 'حسناً'
            }));
        },
        toast: function(msg, type){
            type = type || 'ok';
            var Toast = Swal.mixin({
                toast: true,
                position: 'top-start',
                showConfirmButton: false,
                timer: 3200,
                timerProgressBar: true,
                didOpen: function(t){
                    t.addEventListener('mouseenter', Swal.stopTimer);
                    t.addEventListener('mouseleave', Swal.resumeTimer);
                },
                customClass: {
                    popup: 'sk-toast-popup sk-toast-' + type,
                    title: 'sk-toast-title',
                    timerProgressBar: 'sk-toast-progress'
                }
            });
            Toast.fire({ icon: undefined, title: msg });
        }
    };
})();

/* ═══════════════════════════════════════════
   GLOBAL DELETE / CONFIRM HANDLER
   أي form أو رابط عنده data-confirm
   ═══════════════════════════════════════════ */
(function(){
    document.addEventListener('submit', function(e){
        var form = e.target;
        if (!form || !form.matches('form[data-confirm]')) return;

        var msg      = form.dataset.confirm || 'هل أنت متأكد؟';
        var title    = form.dataset.confirmTitle || 'تأكيد';
        var btn      = form.dataset.confirmBtn || 'نعم، متأكد';
        var icon     = form.dataset.confirmIcon || 'warning';
        var type     = form.dataset.confirmType || 'confirm'; 

        if (form.dataset.confirmed === '1') {
            form.dataset.confirmed = '';
            return;
        }
        e.preventDefault();

        var fn = type === 'danger' ? SKSwal.danger : SKSwal.confirm;
        fn({ title: title, text: msg, confirmButtonText: btn, icon: icon })
            .then(function(r){
                if (r.isConfirmed) {
                    form.dataset.confirmed = '1';
                    if (form.requestSubmit) form.requestSubmit();
                    else form.submit();
                }
            });
    });
})();

/* ═══════════════════════════════════════════
   AJAX POST HELPER
   ═══════════════════════════════════════════ */
window.SKAjax = function(url, data){
    var body = new FormData();
    for (var k in data) body.append(k, data[k]);
    body.append('csrf', window.SK_CSRF || '<?= csrf_token() ?>');
    return fetch(url, { method: 'POST', body: body, credentials: 'same-origin' })
        .then(function(r){
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        });
};

/* ═══════════════════════════════════════════
   SIDEBAR TOGGLE
   ═══════════════════════════════════════════ */
(function(){
    var body   = document.getElementById('adminBody');
    var toggle = document.getElementById('sbToggle');
    var close  = document.getElementById('sbClose');
    if (!body) return;

    if (window.innerWidth > 900 && localStorage.getItem('sk_sb') === '1') {
        body.classList.add('sb-collapsed');
    }

    if (toggle) {
        toggle.addEventListener('click', function(){
            if (window.innerWidth <= 900) {
                body.classList.toggle('sb-mobile-open');
            } else {
                body.classList.toggle('sb-collapsed');
                localStorage.setItem('sk_sb', body.classList.contains('sb-collapsed') ? '1' : '0');
            }
        });
    }

    if (close) {
        close.addEventListener('click', function(){
            body.classList.remove('sb-mobile-open');
        });
    }

    document.addEventListener('click', function(e){
        if (window.innerWidth <= 900
            && body.classList.contains('sb-mobile-open')
            && !e.target.closest('#sidebar')
            && !e.target.closest('#sbToggle')) {
            body.classList.remove('sb-mobile-open');
        }
    });

    window.addEventListener('resize', function(){
        if (window.innerWidth > 900) body.classList.remove('sb-mobile-open');
    });
})();

/* ═══════════════════════════════════════════
   HEADER DROPDOWNS
   ═══════════════════════════════════════════ */
(function(){
    var dropdowns = [
        { btn: 'tbNotifBtn', menu: 'tbNotifMenu' },
        { btn: 'tbUserBtn',  menu: 'tbUserMenu'  }
    ];

    function closeAll(except) {
        dropdowns.forEach(function(d){
            var menu = document.getElementById(d.menu);
            var btn  = document.getElementById(d.btn);
            if (menu && menu !== except) {
                menu.classList.remove('open');
                menu.setAttribute('aria-hidden', 'true');
            }
            if (btn && menu !== except) {
                btn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    dropdowns.forEach(function(d){
        var btn  = document.getElementById(d.btn);
        var menu = document.getElementById(d.menu);
        if (!btn || !menu) return;

        btn.addEventListener('click', function(e){
            e.stopPropagation();
            var isOpen = menu.classList.contains('open');
            closeAll();
            if (!isOpen) {
                menu.classList.add('open');
                menu.setAttribute('aria-hidden', 'false');
                btn.setAttribute('aria-expanded', 'true');
            }
        });
        menu.addEventListener('click', function(e){ e.stopPropagation(); });
    });

    document.addEventListener('click', function(){ closeAll(); });
    document.addEventListener('keydown', function(e){
        if (e.key === 'Escape') closeAll();
    });
})();
</script>
</body>
</html>