import { loadScript } from "@guestyorg/tokenization-js";

document.addEventListener("DOMContentLoaded", async function () {
  const containerId = "guesty-tokenization-container";
  const providerId = "acct_1OloHTAcnyo9ow0l"; // Replace with your actual provider ID

  try {
    // Load the Guesty Tokenization SDK
    const guestyTokenization = await loadScript();
    console.log("Guesty Tokenization JS SDK is loaded and ready to use");

    // Render the tokenization form
    await guestyTokenization.render({
      containerId: containerId,
      providerId: providerId,
    });
    console.log("Guesty Tokenization form rendered successfully");

    // Handle form submission
    document
      .getElementById("pay-now")
      .addEventListener("click", async function () {
        try {
          const paymentMethod = await guestyTokenization.submit();
          console.log("Payment method received:", paymentMethod);
          // Process payment method via Guesty's API
        } catch (e) {
          console.error("Failed to submit the Guesty Tokenization form", e);
        }
      });
  } catch (error) {
    console.error(
      "Failed to load the Guesty Tokenization JS SDK script",
      error
    );
  }
});
