// ============================================================
//  BLACKPINK Dynamic Site — Script.js
//  Only handles: gallery slider + keyboard nav
//  Member/Album data is now handled by PHP + MySQL
// ============================================================

let currentSlideIndex = 0;

function showSlide(index) {
  const slides     = document.querySelectorAll('.gallery_slide');
  const indicators = document.querySelectorAll('.indicator');

  if (!slides.length) return;

  if (index >= slides.length) {
    currentSlideIndex = 0;
  } else if (index < 0) {
    currentSlideIndex = slides.length - 1;
  } else {
    currentSlideIndex = index;
  }

  slides.forEach(s     => s.classList.remove('active'));
  indicators.forEach(i => i.classList.remove('active'));

  slides[currentSlideIndex].classList.add('active');
  if (indicators[currentSlideIndex]) {
    indicators[currentSlideIndex].classList.add('active');
  }
}

function changeSlide(direction) {
  showSlide(currentSlideIndex + direction);
  resetSlideInterval();
}

function currentSlide(index) {
  showSlide(index);
  resetSlideInterval();
}

let slideInterval = setInterval(() => changeSlide(1), 5000);

function resetSlideInterval() {
  clearInterval(slideInterval);
  slideInterval = setInterval(() => changeSlide(1), 5000);
}

document.addEventListener('DOMContentLoaded', function () {
  showSlide(0);
});

document.addEventListener('keydown', function (e) {
  if (e.key === 'ArrowLeft')  { changeSlide(-1); resetSlideInterval(); }
  if (e.key === 'ArrowRight') { changeSlide(1);  resetSlideInterval(); }
});