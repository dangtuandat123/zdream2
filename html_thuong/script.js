// ===================================================================
// ZDREAM - INTERACTIVE JAVASCRIPT
// ===================================================================

document.addEventListener('DOMContentLoaded', function() {
  // =====================
  // MOBILE MENU
  // =====================
  const menuBtn = document.getElementById('menu-btn');
  const closeMenuBtn = document.getElementById('close-menu-btn');
  const mobileMenu = document.getElementById('mobile-menu');
  const menuOverlay = document.getElementById('menu-overlay');
  const menuLinks = mobileMenu?.querySelectorAll('a');

  function openMenu() {
    mobileMenu?.classList.remove('closed');
    mobileMenu?.classList.add('open');
    menuOverlay?.classList.remove('opacity-0', 'pointer-events-none');
    menuOverlay?.classList.add('opacity-100');
    document.body.style.overflow = 'hidden';
  }

  function closeMenu() {
    mobileMenu?.classList.remove('open');
    mobileMenu?.classList.add('closed');
    menuOverlay?.classList.add('opacity-0', 'pointer-events-none');
    menuOverlay?.classList.remove('opacity-100');
    document.body.style.overflow = '';
  }

  menuBtn?.addEventListener('click', openMenu);
  closeMenuBtn?.addEventListener('click', closeMenu);
  menuOverlay?.addEventListener('click', closeMenu);
  menuLinks?.forEach(link => link.addEventListener('click', closeMenu));

  // =====================
  // HEADER SCROLL
  // =====================
  const header = document.getElementById('header');
  
  window.addEventListener('scroll', function() {
    if (window.scrollY > 20) {
      header?.classList.add('header-scrolled');
    } else {
      header?.classList.remove('header-scrolled');
    }
  }, { passive: true });

  // =====================
  // CAROUSEL
  // =====================
  const carouselTrack = document.getElementById('carousel-track');
  const dots = document.querySelectorAll('.carousel-dot');
  const prevBtn = document.getElementById('carousel-prev');
  const nextBtn = document.getElementById('carousel-next');
  const prevBtnMobile = document.getElementById('carousel-prev-mobile');
  const nextBtnMobile = document.getElementById('carousel-next-mobile');
  
  let currentSlide = 0;
  const totalSlides = dots.length;
  let autoSlideInterval;

  function goToSlide(index) {
    currentSlide = index;
    if (carouselTrack) {
      carouselTrack.style.transform = `translateX(-${currentSlide * 100}%)`;
    }
    updateDots();
  }

  function updateDots() {
    dots.forEach((dot, i) => {
      if (i === currentSlide) {
        dot.classList.remove('w-2', 'bg-white/20');
        dot.classList.add('w-6', 'bg-gradient-to-r', 'from-purple-500', 'to-pink-500');
      } else {
        dot.classList.add('w-2', 'bg-white/20');
        dot.classList.remove('w-6', 'bg-gradient-to-r', 'from-purple-500', 'to-pink-500');
      }
    });
  }

  function nextSlide() {
    goToSlide((currentSlide + 1) % totalSlides);
  }

  function prevSlide() {
    goToSlide((currentSlide - 1 + totalSlides) % totalSlides);
  }

  function startAutoSlide() {
    autoSlideInterval = setInterval(nextSlide, 4000);
  }

  function stopAutoSlide() {
    clearInterval(autoSlideInterval);
  }

  // Event listeners
  dots.forEach((dot, i) => {
    dot.addEventListener('click', () => {
      stopAutoSlide();
      goToSlide(i);
      startAutoSlide();
    });
  });

  prevBtn?.addEventListener('click', () => { stopAutoSlide(); prevSlide(); startAutoSlide(); });
  nextBtn?.addEventListener('click', () => { stopAutoSlide(); nextSlide(); startAutoSlide(); });
  prevBtnMobile?.addEventListener('click', () => { stopAutoSlide(); prevSlide(); startAutoSlide(); });
  nextBtnMobile?.addEventListener('click', () => { stopAutoSlide(); nextSlide(); startAutoSlide(); });

  // Start auto-slide
  if (totalSlides > 0) {
    startAutoSlide();
  }

  // =====================
  // USER DROPDOWN (Desktop)
  // =====================
  const userBtn = document.getElementById('user-menu-btn');
  const userDropdown = document.getElementById('user-dropdown');

  userBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    userDropdown?.classList.toggle('hidden');
  });

  document.addEventListener('click', () => {
    userDropdown?.classList.add('hidden');
  });
});
