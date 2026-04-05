            </main>
        </div>
    </div>

    <!-- Mobile Menu Overlay -->
    <div id="mobile-menu-overlay" class="mobile-menu-overlay">
        <div class="mobile-menu">
            <!-- Header -->
            <div class="mobile-menu-header">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <i class="fas fa-brain text-xl text-white"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-white">PsiManager</h2>
                            <p class="text-xs text-white/70">Gestão Inteligente</p>
                        </div>
                    </div>
                    <button id="close-menu-btn" class="w-10 h-10 flex items-center justify-center rounded-lg bg-white/10 hover:bg-white/20 transition-colors text-white">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="mobile-menu-nav">
                <?php
                $currentPage = basename($_SERVER['PHP_SELF']);
                $mobileMenuItems = [
                    ['icon' => 'fa-home', 'label' => 'Dashboard', 'href' => 'index.php'],
                    ['icon' => 'fa-calendar-alt', 'label' => 'Agenda', 'href' => 'agenda.php'],
                    ['icon' => 'fa-users', 'label' => 'Meus Pacientes', 'href' => 'pacientes.php'],
                    ['icon' => 'fa-user-plus', 'label' => 'Novo Paciente', 'href' => 'cadastro.php'],
                    ['icon' => 'fa-chart-line', 'label' => 'Financeiro', 'href' => 'financeiro.php'],
                    ['icon' => 'fa-file-invoice-dollar', 'label' => 'Cobranças', 'href' => 'cobrancas.php'],
                    ['icon' => 'fa-calculator', 'label' => 'Contador', 'href' => 'contador.php'],
                    ['icon' => 'fa-brain', 'label' => 'Testes Psicológicos', 'href' => 'testes.php'],
                    ['icon' => 'fa-credit-card', 'label' => 'Asaas', 'href' => 'asaas_config.php'],
                ];
                
                foreach ($mobileMenuItems as $item):
                    $isActive = ($currentPage === $item['href']) ? 'active' : '';
                ?>
                <a href="<?php echo $item['href']; ?>" class="mobile-menu-link <?php echo $isActive; ?>">
                    <i class="fas <?php echo $item['icon']; ?>"></i>
                    <span><?php echo $item['label']; ?></span>
                </a>
                <?php endforeach; ?>
            </nav>
            
            <!-- Footer -->
            <div class="p-4 border-t border-gray-100 text-center text-xs text-gray-400">
                <p>&copy; 2025 PsiManager v3.0</p>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="toast-container"></div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    
    <script>
        // ========== Mobile Menu Toggle ==========
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const closeMenuBtn = document.getElementById('close-menu-btn');
        const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');

        function openMobileMenu() {
            mobileMenuOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeMobileMenu() {
            mobileMenuOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', openMobileMenu);
        }

        if (closeMenuBtn) {
            closeMenuBtn.addEventListener('click', closeMobileMenu);
        }

        if (mobileMenuOverlay) {
            mobileMenuOverlay.addEventListener('click', (e) => {
                if (e.target === mobileMenuOverlay) {
                    closeMobileMenu();
                }
            });
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && mobileMenuOverlay.classList.contains('active')) {
                closeMobileMenu();
            }
        });

        // ========== Toast Notification System ==========
        function showToast(message, type = 'info', duration = 3000) {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const icons = {
                success: 'fa-check-circle',
                warning: 'fa-exclamation-triangle',
                error: 'fa-times-circle',
                info: 'fa-info-circle'
            };

            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `
                <i class="fas ${icons[type] || icons.info} toast-icon"></i>
                <span class="toast-message">${message}</span>
                <button onclick="this.parentElement.remove()" class="text-gray-400 hover:text-gray-600 ml-2">
                    <i class="fas fa-times"></i>
                </button>
            `;

            container.appendChild(toast);

            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, duration);
        }

        // ========== Confirm Dialog ==========
        function confirmAction(message, onConfirm) {
            if (confirm(message)) {
                onConfirm();
            }
        }

        // ========== Button Loading State ==========
        function setButtonLoading(button, loading = true) {
            if (loading) {
                button.classList.add('btn-loading');
                button.disabled = true;
            } else {
                button.classList.remove('btn-loading');
                button.disabled = false;
            }
        }

        // ========== Form Validation Helpers ==========
        function validateEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        function validatePhone(phone) {
            return /^\(\d{2}\)\s?\d{4,5}-?\d{4}$/.test(phone);
        }

        function validateCPF(cpf) {
            cpf = cpf.replace(/\D/g, '');
            if (cpf.length !== 11) return false;
            if (/^(\d)\1+$/.test(cpf)) return false;
            return true;
        }

        // ========== Input Masks (jQuery Mask) ==========
        $(document).ready(function() {
            $('.cpf-mask').mask('000.000.000-00');
            $('.phone-mask').mask('(00) 00000-0000');
            $('.money-mask').mask('#.##0,00', { reverse: true });
            
            $('input, select, textarea').on('focus', function() {
                $(this).closest('.form-group').addClass('focused');
            }).on('blur', function() {
                $(this).closest('.form-group').removeClass('focused');
            });
        });

        // ========== Smooth Page Transitions ==========
        document.querySelectorAll('a:not([target="_blank"])').forEach(link => {
            if (link.hostname === window.location.hostname) {
                link.addEventListener('click', function(e) {
                    const main = document.querySelector('main');
                    if (main) {
                        main.style.opacity = '0.5';
                        main.style.transform = 'translateY(10px)';
                    }
                });
            }
        });

        // ========== Search Filter ==========
        function filterCards(searchInput, cardsContainer) {
            const searchTerm = searchInput.value.toLowerCase();
            const cards = cardsContainer.querySelectorAll('[data-searchable]');
            
            cards.forEach(card => {
                const searchableText = card.dataset.searchable.toLowerCase();
                if (searchableText.includes(searchTerm)) {
                    card.style.display = '';
                    card.classList.add('animate-fade-in');
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // ========== Number Animation ==========
        function animateNumber(element, start, end, duration = 1000) {
            const startTime = performance.now();
            
            function update(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const easeOut = 1 - Math.pow(1 - progress, 3);
                const current = start + (end - start) * easeOut;
                element.textContent = formatMoney(current);
                if (progress < 1) {
                    requestAnimationFrame(update);
                }
            }
            
            requestAnimationFrame(update);
        }

        function formatMoney(value) {
            return 'R$ ' + value.toLocaleString('pt-BR', { 
                minimumFractionDigits: 2, 
                maximumFractionDigits: 2 
            });
        }

        // ========== Initialize Animations on Scroll ==========
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in-up');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.card-shadow, .stat-card').forEach(card => {
            observer.observe(card);
        });
    </script>
</body>
</html>
