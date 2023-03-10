# Test
import time
from playwright.sync_api import sync_playwright

def run(playwright):
    chromium = playwright.chromium
    browser = chromium.launch()
    page = browser.new_page()
    page.goto("https://project.patric-404.repl.co/")
    page2 = browser.new_page()
    page2.goto("https://project.patric-404.repl.co/")
    time.sleep(300)
    browser.close()

with sync_playwright() as playwright:
    run(playwright)
