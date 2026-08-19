#!/usr/bin/env python3
"""Replace repeated article images in content manifests with matched Commons media."""

from __future__ import annotations

import html
import json
import re
import sys
import time
import urllib.parse
import urllib.request
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
CONTENT_DIR = ROOT / "database" / "content"
USER_AGENT = "JapanTripToolsContentAudit/1.0 (https://japantriptools.com/)"

REPLACEMENTS = {
    "sapporo-first-time-city-and-nature-guide": "Sapporo TV Tower",
    "sapporo-first-night-food-and-transit": "Susukino",
    "setouchi-island-hopping-planning-basics": "Setonaikai National Park",
    "sapporo-and-otaru-winter-short-trip": "Otaru",
    "ic-cards-and-cash-travel-checklist": "Suica",
    "jr-pass-value-check-for-first-trips": "Japan Rail Pass",
    "japan-shinkansen-seat-reservation-basics": "N700 Series Shinkansen",
    "okinawa-useful-information-safety-guide": "Okinawa Island",
    "okinawa-rain-and-typhoon-season-planning": "Naha",
    "hokkaido-first-timer-distance-reality-check": "Daisetsuzan National Park",
    "osaka-street-food-shopping-guide": "Dotonbori",
    "kansai-first-route-planner": "Osaka Station",
    "kyoto-early-morning-temple-strategy": "Fushimi Inari-taisha",
    "japan-earthquake-weather-alert-basics": "Japan Meteorological Agency",
    "tax-free-shopping-and-souvenir-packing": "Ginza",
    "luggage-forwarding-and-hotel-transfer-plan": "Haneda Airport",
    "japan-travel-budget-category-planner": "Japanese yen",
    "okinawa-main-island-north-south-route": "Nago, Okinawa",
    "okinawa-family-snorkeling-safety-basics": "Kerama Islands",
    "miyako-islands-bridge-route": "Miyako Island",
    "kyoto-night-streets-and-quiet-hours-guide": "Ponto-cho",
    "ishigaki-island-nature-day-plan": "Kabira Bay",
    "naha-first-night-and-monorail-guide": "Okinawa Urban Monorail",
    "shikoku-pilgrimage-sampler-route": "Ishite-ji",
    "hiroshima-peace-and-miyajima-two-day-plan": "Hiroshima Peace Memorial",
    "kanazawa-garden-and-craft-day": "Kenroku-en",
    "furano-and-biei-flower-season-planning": "Farm Tomita",
    "hokkaido-lavender-and-farm-season": "Biei, Hokkaido",
    "biei-blue-pond-and-furano-route": "Blue Pond (Biei)",
    "hokkaido-winter-road-trip-safety": "Niseko",
    "lake-toya-usuzan-volcano-plan": "Mount Usu",
    "hakodate-night-view-and-morning-market": "Mount Hakodate",
    "universal-studios-japan-planning-basics": "Sakurajima Station",
    "nara-deer-park-responsible-visit": "Nara Park",
    "kobe-harbor-and-sannomiya-day-trip": "Kobe Port Tower",
    "kansai-airport-arrival-to-osaka-or-kyoto": "Kansai International Airport",
    "osaka-castle-and-nakanoshima-day": "Nakanoshima",
    "osaka-namba-or-umeda-base-choice": "Umeda",
    "namba-kuromon-market-food-plan": "Shinsaibashi",
    "kyoto-to-nara-day-trip-planner": "Nara Line",
    "kyoto-station-area-hotel-strategy": "Kyoto Tower",
    "kyoto-rainy-day-temples-and-cafes": "Nishiki Market",
    "tokyo-tower-and-roppongi-evening-planner": "Roppongi Hills",
    "tokyo-night-view-route-without-late-transfers": "Tokyo Skytree",
    "tokyo-rainy-day-museums-and-shopping-plan": "Tokyo National Museum",
    "ueno-museum-and-park-morning-plan": "Ueno Park",
    "shinjuku-station-arrival-checklist": "Shinjuku Station",
    "asakusa-nakamise-shopping-street-guide": "Nakamise-dori",
    "tokyo-station-first-day-base-plan": "Tokyo Station",
    "tokyo-48-hour-transit-friendly-itinerary": "Yamanote Line",
}


def request_json(url: str) -> dict:
    request = urllib.request.Request(url, headers={"User-Agent": USER_AGENT})
    with urllib.request.urlopen(request, timeout=20) as response:
        return json.load(response)


def strip_html(value: str) -> str:
    value = re.sub(r"<[^>]+>", "", value or "")
    value = html.unescape(value)
    return re.sub(r"\s+", " ", value).strip()


def file_title_from_url(image_url: str) -> str:
    parts = urllib.parse.urlparse(image_url).path.split("/")
    if "/thumb/" in urllib.parse.urlparse(image_url).path and len(parts) >= 3:
        filename = parts[-2]
    else:
        filename = parts[-1]

    return "File:" + urllib.parse.unquote(filename)


def commons_image_for(page_title: str) -> dict:
    summary_url = "https://en.wikipedia.org/api/rest_v1/page/summary/" + urllib.parse.quote(page_title)
    summary = request_json(summary_url)
    image_url = (summary.get("originalimage") or {}).get("source") or (summary.get("thumbnail") or {}).get("source")

    if not image_url:
        raise RuntimeError(f"No image returned for {page_title}")

    if "/wikipedia/en/" in image_url:
        raise RuntimeError(f"Non-Commons image returned for {page_title}: {image_url}")

    if ".svg" in image_url.lower():
        raise RuntimeError(f"SVG image returned for {page_title}: {image_url}")

    file_title = file_title_from_url(image_url)
    params = urllib.parse.urlencode(
        {
            "action": "query",
            "titles": file_title,
            "prop": "imageinfo",
            "iiprop": "url|extmetadata",
            "iiurlwidth": "960",
            "format": "json",
            "formatversion": "2",
        }
    )
    commons = request_json("https://commons.wikimedia.org/w/api.php?" + params)
    page = (commons.get("query") or {}).get("pages", [{}])[0]
    image_info = (page.get("imageinfo") or [{}])[0]
    metadata = image_info.get("extmetadata") or {}
    license_name = strip_html((metadata.get("LicenseShortName") or {}).get("value", ""))
    artist = strip_html((metadata.get("Artist") or {}).get("value", ""))
    source_url = image_info.get("descriptionurl") or (summary.get("content_urls") or {}).get("desktop", {}).get("page")
    url = image_info.get("thumburl") or image_info.get("url") or image_url

    if not url or not source_url or not license_name:
        raise RuntimeError(f"Incomplete Commons metadata for {page_title}")

    if not artist:
        artist = "Wikimedia Commons contributor"

    return {
        "url": url,
        "license": license_name,
        "attribution": artist,
        "source_url": source_url,
        "license_url": (metadata.get("LicenseUrl") or {}).get("value") or None,
    }


def update_manifest(path: Path, cache: dict[str, dict]) -> int:
    data = json.loads(path.read_text())
    changed = 0

    for article in data.get("articles", []):
        slug = article.get("slug")
        page_title = REPLACEMENTS.get(slug)

        if not page_title:
            continue

        if page_title not in cache:
            cache[page_title] = commons_image_for(page_title)
            time.sleep(0.1)

        image = dict(cache[page_title])
        readable_topic = page_title.replace("-", " ")
        image.update(
            {
                "alt": f"{readable_topic} image for {article['title']}",
                "caption": f"{article['title']} is represented with a matched Wikimedia Commons image of {readable_topic}.",
                "match_query": page_title,
            }
        )
        article["image"] = {key: value for key, value in image.items() if value}
        changed += 1

    if changed:
        path.write_text(json.dumps(data, ensure_ascii=False, indent=2) + "\n")

    return changed


def main() -> int:
    cache: dict[str, dict] = {}
    total = 0

    for path in sorted(CONTENT_DIR.glob("*.json")):
        changed = update_manifest(path, cache)
        if changed:
            print(f"updated {changed:2d} images in {path.relative_to(ROOT)}")
            total += changed

    print(f"updated {total} article image records")
    return 0


if __name__ == "__main__":
    sys.exit(main())
