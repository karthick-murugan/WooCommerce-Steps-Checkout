document.addEventListener("DOMContentLoaded", function () {
  const steps = document.querySelectorAll(".cwcc-step");
  const nextButtons = document.querySelectorAll(".cwcc-next");
  const prevButtons = document.querySelectorAll(".cwcc-prev");
  const placeOrderButton = document.querySelector("#place_order");
  const form = document.querySelector('form[name="checkout"]');
  const stepIndicators = document.querySelectorAll(
    ".cwcc-step-indicators .cwcc-step-indicator"
  );
  let currentStep = 0;

  function showStep(index) {
    steps.forEach((step, i) => {
      step.style.display = i === index ? "block" : "none";
    });
    // Update step indicators
    stepIndicators.forEach((indicator, i) => {
      if (i === index) {
        indicator.classList.add("active");
      } else {
        indicator.classList.remove("active");
      }
    });
  }

  function updateShippingMethods() {
    if (
      !wc_checkout_params ||
      !wc_checkout_params.ajax_url ||
      !wc_checkout_params.update_order_review_nonce
    ) {
      console.error("wc_checkout_params is not properly defined");
      return;
    }

    let params = new URLSearchParams({
      action: "woocommerce_update_order_review",
      security: wc_checkout_params.update_order_review_nonce,
      post_data: jQuery("form.checkout").serialize(),
    });

    let body = params ? params.toString() : "";

    fetch(wc_checkout_params.ajax_url, {
      method: "POST",
      credentials: "same-origin",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
      },
      body: body,
    })
      .then((response) => response.json())
      .then((data) => {
        jQuery(document.body).trigger("update_checkout");
      })
      .catch((error) => {
        console.error("Error:", error);
      });
  }

  nextButtons.forEach((button) => {
    button.addEventListener("click", (e) => {
      e.preventDefault(); // Prevent form submission on next button click
      if (currentStep === 0) {
        // After billing step, update shipping methods
        updateShippingMethods();
      }
      if (currentStep < steps.length - 1) {
        currentStep++;
        showStep(currentStep);
      }
    });
  });

  prevButtons.forEach((button) => {
    button.addEventListener("click", (e) => {
      e.preventDefault(); // Prevent form submission on previous button click
      if (currentStep > 0) {
        currentStep--;
        showStep(currentStep);
      }
    });
  });

  if (placeOrderButton) {
    placeOrderButton.addEventListener("click", (e) => {
      e.preventDefault(); // Prevent the default form submission
      if (currentStep === steps.length - 1) {
        // Remove existing error messages
        clearErrorMessages();

        // Handle the form submission using WooCommerce's AJAX endpoint
        const formData = new FormData(form);
        formData.append(
          "woocommerce-process-checkout-nonce",
          document.querySelector('[name="woocommerce-process-checkout-nonce"]')
            .value
        ); //phpcs:ignore

        fetch(wc_checkout_params.checkout_url, {
          method: "POST",
          body: formData,
          credentials: "same-origin",
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.result === "success") {
              window.location.href = data.redirect; //phpcs:ignore
            } else if (data.result === "failure") {
              displayErrorMessages(data.messages);
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            alert(
              "An error occurred while placing the order. Please try again."
            );
          });
      }
    });
  }

  function displayErrorMessages(messages) {
    const container = document.createElement("div");
    container.innerHTML = messages; //phpcs:ignore
    document.querySelector("#cwcc-checkout-block").prepend(container); //phpcs:ignore
  }

  function clearErrorMessages() {
    const errorContainers = document.querySelectorAll(
      ".wc-block-components-notice-banner.is-error"
    );
    errorContainers.forEach((container) => container.remove());
  }

  showStep(currentStep);

  const countrySelect = document.getElementById("billing_country");
  const stateSelect = document.getElementById("billing_state");
  const stateWrapper = document.querySelector(".cwcc-state-wrapper");

  if (countrySelect) {
    // Fetch countries and populate the country dropdown
    fetch("/wp-json/cwcc/v1/countries")
      .then((response) => response.json())
      .then((data) => {
        for (const [code, name] of Object.entries(data)) {
          const option = document.createElement("option");
          option.value = code;
          option.textContent = name;
          countrySelect.appendChild(option);
        }
      });

    countrySelect.addEventListener("change", function () {
      const country = countrySelect.value;
      if (country) {
        fetch(`/wp-json/cwcc/v1/states?country=${country}`)
          .then((response) => {
            if (!response.ok) {
              throw new Error("Network response was not ok");
            }
            return response.text(); // Read response as text
          })
          .then((text) => {
            try {
              const data = JSON.parse(text); // Try to parse JSON
              stateSelect.innerHTML = ""; // Clear previous states

              // Add default "Select a state" option
              const defaultOption = document.createElement("option");
              defaultOption.value = "";
              defaultOption.textContent = "Select a State";
              stateSelect.appendChild(defaultOption);

              if (Object.keys(data).length === 0) {
                stateWrapper.style.display = "none"; // Hide state select if no states are available
              } else {
                stateWrapper.style.display = "block"; // Show state select
                for (const [code, name] of Object.entries(data)) {
                  const option = document.createElement("option");
                  option.value = code;
                  option.textContent = name;
                  stateSelect.appendChild(option);
                }
              }
            } catch (error) {
              // If JSON parsing fails, handle as no states
              stateSelect.innerHTML = "";
              stateWrapper.style.display = "none";
            }
          })
          .catch((error) => {
            console.error("Error fetching states:", error);
          });
      } else {
        stateWrapper.style.display = "none";
        stateSelect.innerHTML = ""; // Clear states if no country is selected
      }
    });
  }
});
