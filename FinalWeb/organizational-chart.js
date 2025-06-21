document.addEventListener("DOMContentLoaded", () => {
    // Get all department tabs and sections
    const deptTabs = document.querySelectorAll(".dept-tab")
    const deptSections = document.querySelectorAll(".department-section")
    const orgChartContent = document.querySelector(".org-chart-content")
  
    // Add click event listeners to department tabs
    deptTabs.forEach((tab) => {
      tab.addEventListener("click", function () {
        const targetDepartment = this.getAttribute("data-department")
  
        // Update active tab
        deptTabs.forEach((t) => t.classList.remove("active"))
        this.classList.add("active")
  
        // Scroll to target department section
        const targetSection = document.getElementById(`${targetDepartment}-section`)
        if (targetSection) {
          targetSection.scrollIntoView({
            behavior: "smooth",
            block: "start",
          })
        }
      })
    })
  
    // Add scroll event listener to update active tab based on scroll position
    orgChartContent.addEventListener("scroll", function () {
      const scrollTop = this.scrollTop
      const windowHeight = this.clientHeight
  
      deptSections.forEach((section, index) => {
        const sectionTop = section.offsetTop
        const sectionHeight = section.offsetHeight
  
        // Check if section is in view
        if (scrollTop >= sectionTop - windowHeight / 3 && scrollTop < sectionTop + sectionHeight - windowHeight / 3) {
          const department = section.getAttribute("data-department")
  
          // Update active tab
          deptTabs.forEach((tab) => tab.classList.remove("active"))
          const activeTab = document.querySelector(`[data-department="${department}"]`)
          if (activeTab) {
            activeTab.classList.add("active")
          }
        }
      })
    })
  
    // Add zoom functionality for organizational chart images
    const orgChartImages = document.querySelectorAll(".org-chart-image")
  
    orgChartImages.forEach((img) => {
      img.addEventListener("click", function () {
        openZoomModal(this.src, this.alt)
      })
    })
  
    // Create zoom modal
    function createZoomModal() {
      const modal = document.createElement("div")
      modal.className = "zoom-modal"
      modal.innerHTML = `
              <button class="zoom-close">
                  <i class="fas fa-times"></i>
              </button>
              <img src="/placeholder.svg" alt="">
          `
  
      document.body.appendChild(modal)
  
      // Close modal events
      const closeBtn = modal.querySelector(".zoom-close")
      const modalImg = modal.querySelector("img")
  
      closeBtn.addEventListener("click", closeZoomModal)
      modal.addEventListener("click", (e) => {
        if (e.target === modal) {
          closeZoomModal()
        }
      })
  
      modalImg.addEventListener("click", closeZoomModal)
  
      // Keyboard close
      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && modal.classList.contains("active")) {
          closeZoomModal()
        }
      })
  
      return modal
    }
  
    // Open zoom modal
    function openZoomModal(src, alt) {
      let modal = document.querySelector(".zoom-modal")
      if (!modal) {
        modal = createZoomModal()
      }
  
      const modalImg = modal.querySelector("img")
      modalImg.src = src
      modalImg.alt = alt
  
      modal.classList.add("active")
      document.body.style.overflow = "hidden"
    }
  
    // Close zoom modal
    function closeZoomModal() {
      const modal = document.querySelector(".zoom-modal")
      if (modal) {
        modal.classList.remove("active")
        document.body.style.overflow = ""
      }
    }
  
    // Add scroll indicator dots
    function createScrollIndicator() {
      const indicator = document.createElement("div")
      indicator.className = "scroll-indicator"
  
      deptSections.forEach((section, index) => {
        const dot = document.createElement("div")
        dot.className = "scroll-dot"
        dot.setAttribute("data-department", section.getAttribute("data-department"))
  
        if (index === 0) {
          dot.classList.add("active")
        }
  
        dot.addEventListener("click", function () {
          const department = this.getAttribute("data-department")
          const targetSection = document.getElementById(`${department}-section`)
          if (targetSection) {
            targetSection.scrollIntoView({
              behavior: "smooth",
              block: "start",
            })
          }
        })
  
        indicator.appendChild(dot)
      })
  
      document.body.appendChild(indicator)
    }
  
    // Initialize scroll indicator
    createScrollIndicator()
  
    // Update scroll indicator on scroll
    orgChartContent.addEventListener("scroll", function () {
      const scrollTop = this.scrollTop
      const windowHeight = this.clientHeight
      const scrollDots = document.querySelectorAll(".scroll-dot")
  
      deptSections.forEach((section, index) => {
        const sectionTop = section.offsetTop
        const sectionHeight = section.offsetHeight
  
        if (scrollTop >= sectionTop - windowHeight / 3 && scrollTop < sectionTop + sectionHeight - windowHeight / 3) {
          scrollDots.forEach((dot) => dot.classList.remove("active"))
          scrollDots[index].classList.add("active")
        }
      })
    })
  
    // Smooth scroll behavior for better UX
    orgChartContent.style.scrollBehavior = "smooth"
  
    // Add loading animation for images
    orgChartImages.forEach((img) => {
      img.addEventListener("load", function () {
        this.style.opacity = "1"
      })
  
      img.style.opacity = "0"
      img.style.transition = "opacity 0.5s ease"
    })
  })
  