/**
 * Theme JavaScript
 * Handles mobile menu, matter lens interaction, and scroll behaviors
 *
 * @package MeasuredAdvocacy
 */

(function() {
  'use strict';

  // =========================
  // Mobile Menu Toggle
  // =========================
  const header = document.getElementById('site-header');
  const menuToggle = document.querySelector('.header-menu-toggle');
  const mobileMenu = document.getElementById('mobile-menu');

  if (menuToggle && mobileMenu) {
    const iconOpen = menuToggle.querySelector('.header-menu-toggle__icon--open');
    const iconClose = menuToggle.querySelector('.header-menu-toggle__icon--close');

    menuToggle.addEventListener('click', function() {
      const isExpanded = menuToggle.getAttribute('aria-expanded') === 'true';
      const newState = !isExpanded;

      menuToggle.setAttribute('aria-expanded', newState);
      mobileMenu.setAttribute('aria-hidden', !newState);

      if (iconOpen && iconClose) {
        iconOpen.style.display = newState ? 'none' : 'block';
        iconClose.style.display = newState ? 'block' : 'none';
      }

      menuToggle.setAttribute('aria-label', newState ?
        (menuToggle.dataset.menuCloseLabel || 'Close') :
        (menuToggle.dataset.menuOpenLabel || 'Menu')
      );

      document.body.style.overflow = newState ? 'hidden' : '';
    });

    // Close on escape
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && menuToggle.getAttribute('aria-expanded') === 'true') {
        menuToggle.click();
      }
    });
  }

  // =========================
  // Header Compact on Scroll
  // =========================
  if (header) {
    let lastScroll = 0;
    window.addEventListener('scroll', function() {
      const currentScroll = window.pageYOffset;
      if (currentScroll > 80) {
        header.setAttribute('data-compact', 'true');
      } else {
        header.setAttribute('data-compact', 'false');
      }
      lastScroll = currentScroll;
    });
  }

  // =========================
  // Matter Lens Interactive
  // =========================
  const lensApply = document.getElementById('lens-apply');
  const lensResults = document.getElementById('lens-results');
  const lensOptions = document.querySelectorAll('.lens-option');

  if (lensApply && lensResults && lensOptions.length > 0) {
    const state = {
      change: null,
      exposure: null,
      decision: null
    };

    lensOptions.forEach(function(btn) {
      btn.addEventListener('click', function() {
        const dimension = btn.dataset.dimension;
        const value = btn.dataset.value;

        // Deselect siblings
        const siblings = btn.parentElement.querySelectorAll('.lens-option');
        siblings.forEach(function(sib) {
          sib.classList.remove('is-selected');
          sib.setAttribute('aria-pressed', 'false');
        });

        // Select this
        btn.classList.add('is-selected');
        btn.setAttribute('aria-pressed', 'true');

        // Update state
        state[dimension] = value;

        // Enable apply if all three selected
        if (state.change && state.exposure && state.decision) {
          lensApply.disabled = false;
        }
      });
    });

    lensApply.addEventListener('click', function() {
      // Simple demo: show confirmation
      lensResults.hidden = false;
      lensResults.innerHTML = '<div style="padding: var(--space-6); background: var(--color-limestone); border-radius: var(--radius-md); margin-top: var(--space-6);"><p class="body-l"><strong>Selection recorded.</strong> Based on your input, we will surface relevant practice areas, attorneys, and representative matters. <a href="/expertise" class="btn btn--text" style="margin-top: var(--space-3); display: inline-block;">View all expertise →</a></p></div>';

      // Optionally scroll to results
      lensResults.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });
  }

  // =========================
  // GSAP Animations (optional)
  // =========================
  if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
    gsap.registerPlugin(ScrollTrigger);

    // Fade-in sections on scroll
    gsap.utils.toArray('section').forEach(function(section) {
      gsap.from(section, {
        scrollTrigger: {
          trigger: section,
          start: 'top 80%',
          toggleActions: 'play none none none'
        },
        opacity: 0,
        y: 30,
        duration: 0.8,
        ease: 'power2.out'
      });
    });
  }

})();
