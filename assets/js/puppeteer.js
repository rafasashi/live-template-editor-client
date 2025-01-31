const puppeteer = require('puppeteer');

// Get the URL from the command line arguments
const url = process.argv[2]; // URL is passed as the first argument

if (!url) {
    console.log('Error: No URL provided!');
    process.exit(1);
}

(async () => {
    try {
        const browser = await puppeteer.launch({
            headless: true,
            args: ['--no-sandbox', '--disable-setuid-sandbox'],
        });
        const page = await browser.newPage();
        await page.goto(url);
        const content = await page.content();
        console.log(content);
        await browser.close();
    } catch (error) {
        console.error("Error in Puppeteer:", error);
        process.exit(1);
    }
})();
