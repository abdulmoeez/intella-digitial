document.addEventListener("DOMContentLoaded", () => {


        document.querySelectorAll("img").forEach(img => {
        const temp = new Image();
        temp.src = img.src;
    });
    

    function headerHamburger(){
        const hamburger = document.getElementById('hamburger');
    const nav = document.querySelector('.id-nav');

    if (!hamburger || !nav) return;

    hamburger.addEventListener('click', () => {
        nav.classList.toggle('active');
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            nav.classList.remove('active');
        }
    });
    }
    headerHamburger();

    function headerDropdown() {
    const servicesItem = document.querySelector(".id-nav-item.collapsable");
    const dropdown = document.getElementById("services-collapsed");
    const hoverBridge = document.querySelector(".hover-bridge");

    if (!servicesItem || !dropdown || !hoverBridge) return;

    const isDesktop = () => window.innerWidth > 768;

    const showDropdown = () => {
        dropdown.classList.add("active");
    };

    const hideDropdown = () => {
        dropdown.classList.remove("active");
    };

    servicesItem.addEventListener("mouseenter", () => {
        if (!isDesktop()) return;
        showDropdown();
    });

    servicesItem.addEventListener("mouseleave", () => {
        if (!isDesktop()) return;
        setTimeout(() => {
            if (!dropdown.matches(":hover") && !hoverBridge.matches(":hover")) {
                hideDropdown();
            }
        }, 10);
    });

    dropdown.addEventListener("mouseleave", () => {
        if (!isDesktop()) return;
        setTimeout(() => {
            if (!servicesItem.matches(":hover") && !hoverBridge.matches(":hover")) {
                hideDropdown();
            }
        }, 10);
    });

    hoverBridge.addEventListener("mouseleave", () => {
        if (!isDesktop()) return;
        setTimeout(() => {
            if (!servicesItem.matches(":hover") && !dropdown.matches(":hover")) {
                hideDropdown();
            }
        }, 10);
    });
    }
    headerDropdown();


    function openOvalsLp() {
        const ovals = document.querySelectorAll(".idlp-s4-content-d1-oval");

        if (!ovals.length) return;

        // Preload oval images (Even though it still looks a bit shit)
        ovals.forEach(oval => {
            const img = new Image();
            img.src = oval.dataset.image;
        });

        const firstOval = ovals[0];
        firstOval.classList.add("active");
        firstOval.querySelector(".idlp-s4-oval-d1 img").src = "assets/images/home/ids4-up-arrow.svg";

        ovals.forEach(oval => {
            const header = oval.querySelector(".idlp-s4-oval-d1");
            const arrow = header.querySelector("img");

            header.addEventListener("click", () => {
                ovals.forEach(item => {
                    if (item !== oval) {
                        item.classList.remove("active");
                        item.querySelector(".idlp-s4-oval-d1 img").src = "assets/images/home/ids4-down-arrow.svg";
                    }
                });

                const isActive = oval.classList.toggle("active");
                arrow.src = isActive ? "assets/images/home/ids4-up-arrow.svg" : "assets/images/home/ids4-down-arrow.svg";
            });
        });
    }
    openOvalsLp()
    function changeOvalImgLp() {
        const ovals = document.querySelectorAll(".idlp-s4-content-d1-oval");
        const mainImage = document.querySelector(".idlp-s4-content-d1-imgContainer > img");
        const mainIcon = document.querySelector(".idlp-s4-content-d1-imgCon-d1 img");

        if (!ovals.length) return;
        if (!mainImage) return;
        if (!mainIcon) return;

        ovals.forEach(oval => {
            const header = oval.querySelector(".idlp-s4-oval-d1");

            header.addEventListener("click", () => {
                if (oval.dataset.image && oval.dataset.icon) {
                    mainImage.style.opacity = 0;
                    mainIcon.style.opacity = 0;

                    setTimeout(() => {
                        mainImage.src = oval.dataset.image;
                        mainIcon.src = oval.dataset.icon;

                        mainImage.style.opacity = 1;
                        mainIcon.style.opacity = 1;
                    }, 50);
                }
            });
        });
    }
    changeOvalImgLp()

    function moveDotsLp() {

    const dots = document.querySelectorAll(".idlp-s5-d2 .idlp-s5-d2-dot");
    if (!dots.length) return;

    const fill = document.querySelector(".idlp-s5-d2-progress-line");
    if (!fill) return;

    const container = fill.parentElement;
    if (!container) return;

    const steps = document.querySelectorAll(".idlp-s5-d3");
    if (!steps.length) return;

    // Fail safes cuz the console wass fuckin me up
    const fillPositions = Array.from(dots).map(dot => {
        const dotRect = dot.getBoundingClientRect();
        const containerRect = container.getBoundingClientRect();
        return ((dotRect.left + dotRect.width / 2) - containerRect.left)
            / containerRect.width * 100;
    });

    dots.forEach((dot, idx) => {
        dot.addEventListener("click", () => {

            const stepNumber = dot.getAttribute("data-step");

            dots.forEach(d => d.classList.remove("active"));

            for (let i = 0; i <= idx; i++) {
                dots[i].classList.add("active");
            }

            fill.style.width = `${fillPositions[idx]}%`;

            steps.forEach(step => step.classList.remove("active"));

            const activeStep = document.querySelector(
                `.idlp-s5-d3[data-step="${stepNumber}"]`
            );

            if (activeStep) activeStep.classList.add("active");
        });
    });

    dots[0].classList.add("active");
    fill.style.width = `${fillPositions[0]}%`;

    steps.forEach(step => step.classList.remove("active"));
    const firstStep = document.querySelector(`.idlp-s5-d3[data-step="1"]`);
    if (firstStep) firstStep.classList.add("active");
    }
    moveDotsLp()

    function scrollBasedStepper() {

        const section = document.querySelector(".idlp-s5");
        if (!section) return;
    
        const dots = document.querySelectorAll(".idlp-s5-d2-dot");
        if (!dots.length) return;
    
        const fill = document.querySelector(".idlp-s5-d2-progress-line");
        if (!fill) return;
    
        const container = fill.parentElement;
        if (!container) return;
    
        const steps = document.querySelectorAll(".idlp-s5-d3");
        if (!steps.length) return;
    
        const scrollContainer = document.querySelector(".home-page-container");
        if (!scrollContainer) return;
    
        function getFillPositions() {
            return Array.from(dots).map(dot => {
                const dotRect = dot.getBoundingClientRect();
                const containerRect = container.getBoundingClientRect();
    
                return (
                    (dotRect.left + dotRect.width / 2 - containerRect.left) /
                    containerRect.width *
                    100
                );
            });
        }
    
        let fillPositions = getFillPositions();
    
        window.addEventListener("resize", () => {
            fillPositions = getFillPositions();
        });

        scrollContainer.addEventListener("scroll", () => {
    
            const rect = section.getBoundingClientRect();
            const windowHeight = scrollContainer.clientHeight;
    
            const progress = Math.min(
                Math.max(
                    ((windowHeight - rect.top) / (windowHeight + rect.height)) * 1.19,
                    0
                ),
                1
            );
    
            let stepIndex;
    
            if (progress < 0.35) {
                stepIndex = 0;
            } else if (progress < 0.7) {
                stepIndex = 1;
            } else {
                stepIndex = 2;
            }
    
            fill.style.width = `${fillPositions[stepIndex]}%`;
    
            dots.forEach(d => d.classList.remove("active"));
            for (let i = 0; i <= stepIndex; i++) {
                dots[i].classList.add("active");
            }
    
            steps.forEach(step => step.classList.remove("active"));
            steps[stepIndex].classList.add("active");
        });
    }
    
    scrollBasedStepper();

    function testimonialLp() {
        const clients = document.querySelectorAll(".idlp-s7-client");
        const quotes = document.querySelectorAll(".idlp-s7-quote h3");
        const names = document.querySelectorAll(".idlp-s7-name-job > div");

        if (!clients.length) return;
        if (!quotes.length) return;
        if (!names.length) return;

        clients.forEach((client, index) => {
            client.addEventListener("click", () => {


                clients.forEach(c => c.classList.remove("active"));
                quotes.forEach(q => q.classList.remove("active"));
                names.forEach(n => n.classList.remove("active"));


                client.classList.add("active");
                quotes[index].classList.add("active");
                names[index].classList.add("active");
            });
        });
    }
    testimonialLp();

    function autoTestimonialLp() {

    const clients = document.querySelectorAll(".idlp-s7-client");
    const quotes = document.querySelectorAll(".idlp-s7-quote h3");
    const names = document.querySelectorAll(".idlp-s7-name-job > div");

    if (!clients.length || !quotes.length || !names.length) return;

    let currentIndex = 0;
    const total = clients.length;

    function showTestimonial(index) {

        clients.forEach(c => c.classList.remove("active"));
        quotes.forEach(q => q.classList.remove("active"));
        names.forEach(n => n.classList.remove("active"));

        clients[index].classList.add("active");
        quotes[index].classList.add("active");
        names[index].classList.add("active");
    }

    setInterval(() => {
        currentIndex++;
        if (currentIndex >= total) {
            currentIndex = 0;
        }

        showTestimonial(currentIndex);

    }, 8000); // 4 seconds (change this if Amit says)

    }
    autoTestimonialLp();

    function contactUsLp(){
        const dots = document.querySelectorAll(".idlp-s9-dot");
    const reviews = document.querySelectorAll(".idlp-s9-left-review");

    if (!dots.length) return;
        if (!reviews.length) return;

    dots.forEach((dot, index) => {
        dot.addEventListener("click", () => {

            dots.forEach(d => d.classList.remove("active"));
            reviews.forEach(r => r.classList.remove("active"));

            dot.classList.add("active");
            reviews[index].classList.add("active");
        });
    })
    }
    contactUsLp();

    function contactUsCup(){
        const dots = document.querySelectorAll(".idcup-s1-dot");
    const reviews = document.querySelectorAll(".idcup-s1-left-review");

    if (!dots.length) return;
        if (!reviews.length) return;

    dots.forEach((dot, index) => {
        dot.addEventListener("click", () => {

            dots.forEach(d => d.classList.remove("active"));
            reviews.forEach(r => r.classList.remove("active"));

            dot.classList.add("active");
            reviews[index].classList.add("active");
        });
    })
    }
    contactUsCup();

    function cardsCarouselSp() {
    const scrollContainer = document.querySelector(".idsp-s3-d1-inner");
    const leftArrow = document.getElementById("leftArrow");
    const rightArrow = document.getElementById("rightArrow");

    if (!scrollContainer || !leftArrow || !rightArrow) {
        console.log("Carousel elements not found");
        return;
    }

    const scrollAmount = scrollContainer.querySelector(".idsp-s3-d2-card").offsetWidth + 20;


    function updateArrows() {
        const maxScrollLeft = scrollContainer.scrollWidth - scrollContainer.clientWidth;

        if (scrollContainer.scrollLeft > 0) {
            leftArrow.classList.add("active");
        } else {
            leftArrow.classList.remove("active");
        }


        if (scrollContainer.scrollLeft < maxScrollLeft - 1) {
            rightArrow.classList.add("active");
        } else {
            rightArrow.classList.remove("active");
        }
    }


    rightArrow.addEventListener("click", () => {
        scrollContainer.scrollBy({ left: scrollAmount, behavior: "smooth" });
    });


    leftArrow.addEventListener("click", () => {
        scrollContainer.scrollBy({ left: -scrollAmount, behavior: "smooth" });
    });


    scrollContainer.addEventListener("scroll", updateArrows);


    updateArrows();
    } 
    cardsCarouselSp();

    function scrollBasedStepperSp(){

    const section = document.querySelector(".idsp-s4");
    const cardsContainer = document.querySelector(".idsp-s4-cards-inner");
    const cards = document.querySelectorAll(".idsp-s4-card");
    const dots = document.querySelectorAll(".idsp-s4-d2-dot");
    const progressLine = document.querySelector(".idsp-s4-d2-progress-line");
    const dotsContainer = document.querySelector(".idsp-s4-d2");

    if (!cardsContainer || !cards.length || !dots.length || !progressLine || !section || !dotsContainer) return;

    const totalCards = cards.length;

    function getFillPositions() {
        return Array.from(dots).map(dot => {
            const dotRect = dot.getBoundingClientRect();
            const containerRect = dotsContainer.getBoundingClientRect();

            return ((dotRect.left + dotRect.width / 2) - containerRect.left) 
                    / containerRect.width * 100;
        });
    }

    let fillPositions = getFillPositions();

    window.addEventListener("resize", () => {
        fillPositions = getFillPositions();
    });

    window.addEventListener("scroll", () => {

        const rect = section.getBoundingClientRect();
        const windowHeight = window.innerHeight;

        const sectionHeight = section.offsetHeight - windowHeight;

        const scrolled = Math.min(
            Math.max(-rect.top, 0),
            sectionHeight
        );

        const progress = scrolled / sectionHeight;


        const maxScroll = cardsContainer.scrollWidth - cardsContainer.clientWidth;
        cardsContainer.scrollLeft = maxScroll * progress;

        const visibilityProgress = Math.min(
            Math.max((windowHeight - rect.top) / (windowHeight + rect.height), 0),
            1
        );

        const activeIndex = Math.min(
            dots.length - 1,
            Math.floor(visibilityProgress * dots.length)
        );

        cards.forEach((card, i) => {
            card.classList.toggle("active", i === activeIndex);
        });


        dots.forEach(d => d.classList.remove("active"));
        for (let i = 0; i <= activeIndex; i++) {
            dots[i].classList.add("active");
        }

        progressLine.style.width = `${fillPositions[activeIndex]}%`;

    });
    }
    scrollBasedStepperSp();

    function moveDotsIdspS4() {
    const dots = document.querySelectorAll(".idsp-s4-d2-dot");
    if (!dots.length) return;

    const fill = document.querySelector(".idsp-s4-d2-progress-line");
    if (!fill) return;

    const container = fill.parentElement;
    if (!container) return;

    const cards = document.querySelectorAll(".idsp-s4-card");
    const steps = document.querySelectorAll(".idsp-s4-d2-d3 p"); 
    if (!cards.length || !steps.length) return;


    const fillPositions = Array.from(dots).map(dot => {
        const dotRect = dot.getBoundingClientRect();
        const containerRect = container.getBoundingClientRect();
        return ((dotRect.left + dotRect.width / 2) - containerRect.left)
            / containerRect.width * 100;
    });

    dots.forEach((dot, idx) => {
        dot.addEventListener("click", () => {
            const stepNumber = dot.getAttribute("data-step");


            dots.forEach(d => d.classList.remove("active"));
            for (let i = 0; i <= idx; i++) {
                dots[i].classList.add("active");
            }

            fill.style.width = `${fillPositions[idx]}%`;

            cards.forEach(card => card.classList.remove("active"));
            const activeCard = document.querySelector(`.idsp-s4-card[data-step="${stepNumber}"]`);
            if (activeCard) activeCard.classList.add("active");

            // Update steps active state
            steps.forEach(step => step.classList.remove("active"));
            const activeStep = steps[idx]; // steps are in order, 0-based
            if (activeStep) activeStep.classList.add("active");

            // Scroll card into view (centered)
            if (activeCard) {
                const containerWidth = activeCard.parentElement.clientWidth;
                const cardCenter = activeCard.offsetLeft + activeCard.offsetWidth / 2;
                const targetScroll = cardCenter - containerWidth / 2;
                activeCard.parentElement.scrollTo({
                    left: targetScroll,
                    behavior: "smooth"
                });
            }
        });
    });

    dots[0].classList.add("active");
    fill.style.width = `${fillPositions[0]}%`;
    if (cards[0]) cards[0].classList.add("active");
    if (steps[0]) steps[0].classList.add("active");
     }
     moveDotsIdspS4();

     document.querySelectorAll(".idsp-s7-faq").forEach(faq => {
    faq.addEventListener("click", () => {

        const isActive = faq.classList.contains("active");

        // OPTIONAL: Close all other FAQs (accordion behavior)
        document.querySelectorAll(".idsp-s7-faq").forEach(item => {
            item.classList.remove("active");
            item.querySelector("img").src = "assets/images/services/idsp-s7-downArrow.svg";
        });

        // If it was not active, open it
        if (!isActive) {
            faq.classList.add("active");
            faq.querySelector("img").src = "assets/images/services/idsp-s7-upArrow.svg";
        }

        });
    });

    function formSubmitMessage() {
    const forms = document.querySelectorAll(".contact-form");
    if (!forms.length) return;

    forms.forEach(form => {
        const parent = form.parentElement;

        // Create alert for idlp-s9-right
        let alertBoxS9 = null;
        if (parent.classList.contains("idlp-s9-right")) {
            alertBoxS9 = document.createElement("div");
            alertBoxS9.classList.add("form-alert-idlp-s9-right");
            alertBoxS9.innerText = "Message sent successfully!";
            alertBoxS9.style.display = "none"; // hide initially
            parent.prepend(alertBoxS9);
        }

        // Create alert for idcup-s1-right
        let alertBoxCup = null;
        if (parent.classList.contains("idcup-s1-right")) {
            alertBoxCup = document.createElement("div");
            alertBoxCup.classList.add("form-alert-idcup-s1-right");
            alertBoxCup.innerText = "Message sent successfully!";
            alertBoxCup.style.display = "none"; // hide initially
            parent.prepend(alertBoxCup);
        }

        form.addEventListener("submit", function (e) {
        e.preventDefault();

            if (alertBoxS9) {
                alertBoxS9.style.display = "block";
                alertBoxS9.classList.add("show");
            }

            if (alertBoxCup) {
                alertBoxCup.style.display = "block";
                alertBoxCup.classList.add("show");
            }

            form.reset();
            });
        });
    }
    formSubmitMessage();


    function portfolioLoadMore() {

        // loop through EVERY portfolio section
        const portfolioSections = document.querySelectorAll(".idportp-s2-display-D");
    
        portfolioSections.forEach(section => {
    
            const cards = section.querySelectorAll(".idportp-s2-display-D-d1");
            const loadBtn = section.querySelector("button");
    
            // skip if button doesn't exist
            if (!loadBtn) return;
    
            const loadBtnText = loadBtn.querySelector("p");
    
            const increment = 3;
    
            let visibleCount = increment;
    
            updateCards();
    
            loadBtn.addEventListener("click", () => {
    
                // reset
                if (visibleCount >= cards.length) {
    
                    visibleCount = increment;
    
                    loadBtnText.textContent = "Load more";
    
                }
    
                // show next batch
                else {
    
                    visibleCount += increment;
    
                    if (visibleCount >= cards.length) {
                        loadBtnText.textContent = "Show less";
                    }
    
                }
    
                updateCards();
    
            });
    
            function updateCards() {
    
                cards.forEach((card, index) => {
    
                    if (index < visibleCount) {
                        card.style.display = "flex";
                    }
    
                    else {
                        card.style.display = "none";
                    }
    
                });
    
            }
    
        });
    
    }
    portfolioLoadMore();


    function portfolioTabs() {

        const toggles = document.querySelectorAll(".idportp-s2-d1-toggles");
        const portfolioSections = document.querySelectorAll(".idportp-s2-display-D");

        if (!toggles.length || !portfolioSections.length) return;
    
        toggles[0].classList.add("active");
    

        portfolioSections.forEach((section, index) => {
    
            if(index === 0){
                section.style.display = "flex";
            }
            
            else{
                section.style.display = "none";
            }
    
        });
    
        toggles.forEach(toggle => {
    
            toggle.addEventListener("click", () => {
    
                const portfolioName = toggle.dataset.idportpPortfolio;

                toggles.forEach(item => {
                    item.classList.remove("active");
                });

                toggle.classList.add("active");
    
                // show matching section only
                portfolioSections.forEach(section => {
    
                    if(section.dataset.idportpPortfolio === portfolioName){
    
                        section.style.display = "flex";
                    }
    
                    else{
    
                        section.style.display = "none";
    
                    }
    
                });
    
            });
    
        });
    
    }
    portfolioTabs();

    function CSRBLcontentScrollNav() {
        const navItems = document.querySelectorAll(
            ".idcsRbl-s2-d1-d2 div[data-CS-RBL-Cont]"
        );
    
        const scrollContainer = document.querySelector(
            ".case-study-rbl-page-container"
        );
    
        if (!navItems.length || !scrollContainer) return;
    
        navItems.forEach(item => {
            item.addEventListener("click", () => {
                navItems.forEach(el => el.classList.remove("active"));
                item.classList.add("active");
    
                const targetValue = item.getAttribute("data-CS-RBL-Cont");
    
                const targetSection = document.querySelector(
                    `.idcsRbl-s2-d2 [data-CS-RBL-Cont="${targetValue}"],
                    [data-CS-RBL-Cont="${targetValue}"].idcsRbl-s2-d2-CP,
                    [data-CS-RBL-Cont="${targetValue}"].idcsRbl-s2-d2-CH,
                    [data-CS-RBL-Cont="${targetValue}"].idcsRbl-s2-d2-RES,
                    [data-CS-RBL-Cont="${targetValue}"].idcsRbl-s2-d2-CON`
                );
    
                if (targetSection) {
    
                    const containerRect =
                        scrollContainer.getBoundingClientRect();
    
                    const targetRect =
                        targetSection.getBoundingClientRect();
    
                    const scrollPos =
                        targetRect.top -
                        containerRect.top +
                        scrollContainer.scrollTop;
    
                    scrollContainer.scrollTo({
                        top: scrollPos,
                        behavior: "smooth"
                    });
                }
            });
        });
    }
    
    CSRBLcontentScrollNav();

    function CSRBLSlider() {

        const slider = document.querySelector(".idcsRbl-s3-d1-cards");
        const leftArrow = document.getElementById("leftArrow");
        const rightArrow = document.getElementById("rightArrow");

        if (!slider || !leftArrow || !rightArrow) return;

        // Dynamic value rather than a hardcoded scroll amount!
        function getScrollAmount() {

            const firstCard = slider.querySelector(".idcsRbl-s3-d1-card");

            if (!firstCard) return 300;

            const cardStyles = window.getComputedStyle(firstCard);
            const cardMarginRight = parseFloat(cardStyles.marginRight) || 0;

            return firstCard.offsetWidth + cardMarginRight + 20;
        }

        function updateArrows() {

            const maxScrollLeft = slider.scrollWidth - slider.clientWidth;

            if (slider.scrollLeft <= 0) {
                leftArrow.classList.remove("active");
            } else {
                leftArrow.classList.add("active");
            }

            if (slider.scrollLeft >= maxScrollLeft - 2) {
                rightArrow.classList.remove("active");
            } else {
                rightArrow.classList.add("active");
            }
        }

        rightArrow.addEventListener("click", () => {
            if (!rightArrow.classList.contains("active")) return;
            slider.scrollBy({
                left: getScrollAmount(),
                behavior: "smooth"
            });
        });

        leftArrow.addEventListener("click", () => {
            if (!leftArrow.classList.contains("active")) return;
            slider.scrollBy({
                left: -getScrollAmount(),
                behavior: "smooth"
            });
        });
        slider.addEventListener("scroll", updateArrows);
        window.addEventListener("resize", updateArrows);
        updateArrows();
    }CSRBLSlider();

    function caseStudiesSlider() {

        const dots = document.querySelectorAll(".idCSs-s1-d3 div");
        const slider = document.querySelector(".idCSs-s1-d2-inner");
        if (!dots.length || !slider) return;
        let currentIndex = 0;
        dots[0].classList.add("active");
        dots.forEach((dot, index) => {
            dot.addEventListener("click", () => {
                currentIndex = index;
                dots.forEach(item => {
                    item.classList.remove("active");
                })
                dot.classList.add("active");
                slider.style.transform = `translateX(-${currentIndex * 100}%)`;
            });
        });
    }
    caseStudiesSlider();

    function caseStudiesFilterAndPagination() {

        const filterButtons = document.querySelectorAll(".idCSs-s2-d1 div");
        const cards = document.querySelectorAll(".idCSs-s2-d2-card");

        const paginationContainer = document.querySelector(".idCSs-s2-d3");
        const prevBtn = document.querySelector(".idCSs-s2-d3-prev");
        const nextBtn = document.querySelector(".idCSs-s2-d3-next");

        if (
            !filterButtons.length ||
            !cards.length ||
            !paginationContainer ||
            !prevBtn ||
            !nextBtn
        ) return;

        const cardsPerPage = 9;
        let activeFilters = ["all"];
        let currentPage = 1;
        let filteredCards = [...cards];


        filterButtons.forEach(button => {

            button.addEventListener("click", () => {
                const filter = button.dataset.idcssCard;
                if (filter === "all") {
                    activeFilters = ["all"];
                    filterButtons.forEach(btn => {
                        btn.classList.remove("active");
                    });
                    button.classList.add("active");
                }

                else {
                    const allButton = document.querySelector(
                        '.idCSs-s2-d1 div[data-idcss-card="all"]'
                    );
                    allButton.classList.remove("active");
                    activeFilters = activeFilters.filter(item => item !== "all");

                    if (activeFilters.includes(filter)) {
                        activeFilters = activeFilters.filter(item => item !== filter);
                        button.classList.remove("active");
                    } else {
                        activeFilters.push(filter);
                        button.classList.add("active");
                    }

                    if (activeFilters.length === 0) {
                        activeFilters = ["all"];
                        allButton.classList.add("active");
                    }
                }

                currentPage = 1;
                updateCards();
            });
        });


        function updateCards() {

            if (activeFilters.includes("all")) {
                filteredCards = [...cards];
            } else {

                filteredCards = [...cards].filter(card => {
                    const cardCategory = card.dataset.idcssCard;
                    return activeFilters.includes(cardCategory);
                });

            }

            cards.forEach(card => {
                card.style.display = "none";
            });
            const start = (currentPage - 1) * cardsPerPage;
            const end = start + cardsPerPage;
            const cardsToShow = filteredCards.slice(start, end);
            cardsToShow.forEach(card => {
                card.style.display = "flex";
            });
            generatePagination();
        }


        function generatePagination() {
            document.querySelectorAll(".idCSs-s2-d3-num").forEach(item => {
                item.remove();
            });

            const totalPages = Math.ceil(filteredCards.length / cardsPerPage);
            for (let i = 1; i <= totalPages; i++) {
                const pageBtn = document.createElement("div");
                pageBtn.classList.add("idCSs-s2-d3-num");
                if (i === currentPage) {
                    pageBtn.classList.add("active");
                }
                pageBtn.innerHTML = `<p>${i}</p>`;
                paginationContainer.insertBefore(pageBtn, nextBtn);

                pageBtn.addEventListener("click", () => {
                    currentPage = i;
                    updateCards();
                });

            }


            prevBtn.style.opacity = currentPage === 1 ? "0.5" : "1";
            nextBtn.style.opacity =
                currentPage === totalPages ? "0.5" : "1";

        }


        prevBtn.addEventListener("click", () => {

            if (currentPage === 1) return;

            currentPage--;

            updateCards();

        });


        nextBtn.addEventListener("click", () => {

            const totalPages = Math.ceil(
                filteredCards.length / cardsPerPage
            );

            if (currentPage === totalPages) return;

            currentPage++;

            updateCards();

        });


        const allButton = document.querySelector(
            '.idCSs-s2-d1 div[data-idcss-card="all"]'
        );

        allButton.classList.add("active");

        updateCards();

    }
    caseStudiesFilterAndPagination();

    function BspDropdownSearch() {

        const dropdownToggle = document.querySelector(".isbsp-s1-d1-d1-d1");
        const dropdownLabel = dropdownToggle?.querySelector(":scope > p");
        const dropdownItems = document.querySelectorAll(
            ".isbsp-s1-d1-d1-d1-dropdown [data-Bs-dropdown]"
        );
        const searchInput = document.querySelector(".isbsp-s1-d1-d1-input");
        const filterButtons = document.querySelectorAll(".isbsp-s1-d1-d1-options div");

        if (!dropdownToggle || !searchInput || !dropdownItems.length) {
            return null;
        }

        let selectedCategories = [];
        let searchTerm = "";
        const changeListeners = [];

        function notifyChange() {
            changeListeners.forEach(fn => fn());
        }

        function updateDropdownLabel() {
            if (!dropdownLabel) return;
            const count = selectedCategories.length;
            dropdownLabel.textContent = count === 0 ? "All" : String(count);
        }

        function syncActiveStates() {

            const showAll = selectedCategories.length === 0;

            filterButtons.forEach(btn => {
                const filter = btn.dataset.bsSearch || "all";

                if (filter === "all") {
                    btn.classList.toggle("active", showAll);
                } else {
                    btn.classList.toggle("active", selectedCategories.includes(filter));
                }
            });

            dropdownItems.forEach(item => {
                const filter = item.dataset.bsDropdown;
                item.classList.toggle("active", selectedCategories.includes(filter));
            });
        }

        function toggleCategory(category) {

            if (selectedCategories.includes(category)) {
                selectedCategories = selectedCategories.filter(item => item !== category);
            } else {
                selectedCategories = [...selectedCategories, category];
            }

            updateDropdownLabel();
            syncActiveStates();
            notifyChange();
        }

        function clearCategories() {
            selectedCategories = [];
            updateDropdownLabel();
            syncActiveStates();
            notifyChange();
        }

        dropdownItems.forEach(item => {

            item.addEventListener("click", e => {
                e.stopPropagation();
                toggleCategory(item.dataset.bsDropdown);
            });

        });

        filterButtons.forEach(btn => {

            btn.addEventListener("click", () => {

                const filter = btn.dataset.bsSearch || "all";

                if (filter === "all") {
                    clearCategories();
                } else {
                    toggleCategory(filter);
                }

            });

        });

        searchInput.addEventListener("input", () => {
            searchTerm = searchInput.value.trim().toLowerCase();
            notifyChange();
        });

        updateDropdownLabel();
        syncActiveStates();

        return {
            onChange(callback) {
                changeListeners.push(callback);
            },
            cardMatches(card) {

                const category = card.dataset.bsDropdown;
                const categoryMatch =
                    selectedCategories.length === 0 ||
                    selectedCategories.includes(category);

                if (!categoryMatch) return false;

                if (!searchTerm) return true;

                return card.textContent.toLowerCase().includes(searchTerm);
            }
        };
    }

    function blogsFilterAndPagination() {

        const filterControl = BspDropdownSearch();
        const cards = document.querySelectorAll(".isbsp-s2-d1-card");

        const paginationContainer = document.querySelector(".isbsp-s2-d2");
        const prevBtn = document.querySelector(".isbsp-s2-d2-prev");
        const nextBtn = document.querySelector(".isbsp-s2-d2-next");

        if (
            !filterControl ||
            !cards.length ||
            !paginationContainer ||
            !prevBtn ||
            !nextBtn
        ) return;

        const cardsPerPage = 9;

        let currentPage = 1;
        let filteredCards = [...cards];

        filterControl.onChange(() => {
            currentPage = 1;
            updateCards();
        });


        function updateCards() {

            filteredCards = [...cards].filter(card => filterControl.cardMatches(card));

            cards.forEach(card => {
                card.style.display = "none";
            });

            const start = (currentPage - 1) * cardsPerPage;
            const end = start + cardsPerPage;

            const cardsToShow = filteredCards.slice(start, end);

            cardsToShow.forEach(card => {
                card.style.display = "flex";
            });

            generatePagination();
        }


        function generatePagination() {

            document.querySelectorAll(".isbsp-s2-d2-num").forEach(item => {
                item.remove();
            });

            const totalPages = Math.ceil(filteredCards.length / cardsPerPage);

            for (let i = 1; i <= totalPages; i++) {

                const pageBtn = document.createElement("div");

                pageBtn.classList.add("isbsp-s2-d2-num");

                if (i === currentPage) {
                    pageBtn.classList.add("active");
                }

                pageBtn.innerHTML = `<p>${i}</p>`;

                paginationContainer.insertBefore(pageBtn, nextBtn);

                pageBtn.addEventListener("click", () => {

                    currentPage = i;

                    updateCards();

                });

            }

            prevBtn.style.opacity = currentPage === 1 ? "0.5" : "1";

            nextBtn.style.opacity =
                currentPage === totalPages ? "0.5" : "1";

        }


        prevBtn.addEventListener("click", () => {

            if (currentPage === 1) return;

            currentPage--;

            updateCards();

        });


        nextBtn.addEventListener("click", () => {

            const totalPages = Math.ceil(
                filteredCards.length / cardsPerPage
            );

            if (currentPage === totalPages) return;

            currentPage++;

            updateCards();

        });

        updateCards();

    }
    blogsFilterAndPagination();

    function setupSearchDropdown(
        dropdownBtnSelector,
        searchWrapperSelector,
        searchIconSelector,
        optionsSelector,
        dropdownSelector,
        searchInputSelector){
            const dropdownBtn = document.querySelector(dropdownBtnSelector);
            const searchWrapper = document.querySelector(searchWrapperSelector);
            const searchIcon = document.querySelector(searchIconSelector);
            const options = document.querySelector(optionsSelector);
            const dropdown = document.querySelector(dropdownSelector);
            const searchInput = document.querySelector(searchInputSelector);

            if (!dropdownBtn || !searchWrapper || !searchIcon || !options || !dropdown || !searchInput) {
                
                return;
            }
        

            dropdownBtn.addEventListener("click", () => {
                dropdownBtn.classList.toggle("active");
            });

            document.addEventListener("click", (e) => {
                if(!dropdownBtn.contains(e.target)){
                    dropdownBtn.classList.remove("active");
                }
            });

            searchIcon.addEventListener("click", () => {
                searchWrapper.classList.toggle("active");
                if(searchWrapper.classList.contains("active")){
                    options.style.display = "none";
                    dropdown.style.display = "flex";
                    searchInput.style.display = "block";
                }else{
                    options.style.display = "flex";
                    dropdown.style.display = "none";
                    searchInput.style.display = "none";
                }
            });
        }
        setupSearchDropdown(
        ".isbsp-s1-d1-d1-d1",
        ".isbsp-s1-d1-d1",
        ".isbsp-s1-d1-d1 > svg",
        ".isbsp-s1-d1-d1-options",
        ".isbsp-s1-d1-d1-d1",
        ".isbsp-s1-d1-d1-input"
        );

    function blogsPageContentScroll() {
        const tocItems = document.querySelectorAll(
            ".idBDs-s2-d1 li[data-CSs-scroll]"
        );
    
        const scrollContainer = document.querySelector(
            ".blog-details-page-container"
        );
    
        tocItems.forEach(item => {
            item.addEventListener("click", () => {
    
                // Remove previous active
                tocItems.forEach(li => li.classList.remove("active"));
    
                // Add active to clicked li
                item.classList.add("active");
    
                const target = document.querySelector(
                    `.idBDs-s2-d2 [data-CSs-scroll="${item.dataset.cssScroll}"]`
                );
    
                if (target) {
    
                    const containerRect =
                        scrollContainer.getBoundingClientRect();
    
                    const targetRect =
                        target.getBoundingClientRect();
    
                    const scrollPos =
                        targetRect.top -
                        containerRect.top +
                        scrollContainer.scrollTop;
    
                    scrollContainer.scrollTo({
                        top: scrollPos,
                        behavior: "smooth"
                    });
                }
            });
        });
    }
    
    blogsPageContentScroll();

        
    function headerScrollEffect() {
        const header = document.querySelector(".idlp-header");
        if (!header) return;
    
        const candidates = [
            document.querySelector(".home-page-container"),
            document.querySelector(".blog-details-page-container"),
            document.querySelector(".case-study-rbl-page-container"),
            document.querySelector(".about-page-container"),
            document.querySelector(".blogs-page-container"),
            document.querySelector(".case-studies-page-container"),
            document.querySelector(".contact-us-page-container"),
            document.querySelector(".portfolio-page-container"),
            document.querySelector(".services-page-container")
        ];
    
        // pick the one that actually scrolls
        const scrollContainer = candidates.find(el => {
            if (!el) return false;
            return el.scrollHeight > el.clientHeight;
        }) || window;
    
        function onScroll() {
            const scrollTop =
                scrollContainer === window
                    ? window.scrollY
                    : scrollContainer.scrollTop;
    
            header.classList.toggle("scrolled", scrollTop > 10);
        }
    
        scrollContainer.addEventListener("scroll", onScroll, { passive: true });
        onScroll();
    }
    
    headerScrollEffect();

});