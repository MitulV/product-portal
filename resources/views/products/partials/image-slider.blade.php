@php
    $imageUrls = $images->pluck('file_url')->values()->toArray();
@endphp

@if (isset($images) && count($imageUrls) > 0)
    <div class="w-full relative">
        <div class="swiper product-image-carousel swiper-container relative">
            <div class="swiper-wrapper">
                @foreach ($imageUrls as $index => $imageUrl)
                    <div class="swiper-slide">
                        <div class="bg-slate-100 rounded-lg h-[500px] flex justify-center items-center overflow-hidden">
                            <img src="{{ $imageUrl }}" alt="Product Image {{ $index + 1 }}"
                                class="max-w-full max-h-full w-auto h-auto object-contain"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />
                            <div class="hidden w-full h-full items-center justify-center bg-slate-100">
                                <span class="text-slate-400">Image not available</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if (count($imageUrls) > 1)
                <div class="swiper-pagination !bottom-4"></div>
            @endif
        </div>


    </div>

    <style>
        .product-image-carousel .swiper-wrapper {
            width: 100%;
            height: max-content !important;
            padding-bottom: 64px !important;
            -webkit-transition-timing-function: linear !important;
            transition-timing-function: linear !important;
            position: relative;
        }

        .product-image-carousel .swiper-pagination-bullet {
            background: #2563eb;
            width: 8px;
            height: 8px;
        }

        .product-image-carousel .swiper-pagination-bullet-active {
            background: #2563eb !important;
        }

        .product-image-carousel .swiper-pagination {
            position: absolute;
            bottom: 1rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
        }

        .product-image-carousel .swiper-slide {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-image-carousel .swiper-slide>div {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productCarousel = new Swiper(".product-image-carousel", {
                loop: {{ count($imageUrls) > 1 ? 'true' : 'false' }},
                spaceBetween: 10,
                autoplay: {
                    delay: 3000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: ".product-image-carousel .swiper-pagination",
                    clickable: true,
                },
            });

            @if (count($imageUrls) > 1)
                // Update active thumbnail on slide change
                productCarousel.on('slideChange', function() {
                    const activeIndex = productCarousel.realIndex;
                    document.querySelectorAll('.product-thumbnail').forEach((thumb, index) => {
                        if (index === activeIndex) {
                            thumb.classList.add('active', 'border-blue-600');
                            thumb.classList.remove('border-slate-200');
                        } else {
                            thumb.classList.remove('active', 'border-blue-600');
                            thumb.classList.add('border-slate-200');
                        }
                    });
                });

                // Click thumbnail to go to slide
                document.querySelectorAll('.product-thumbnail').forEach((thumb, index) => {
                    thumb.addEventListener('click', function() {
                        productCarousel.slideToLoop(index);
                    });
                });
            @endif
        });
    </script>
@endif
