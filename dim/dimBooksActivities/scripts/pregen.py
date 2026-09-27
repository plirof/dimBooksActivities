#!/usr/bin/env python3
import argparse
import asyncio
import os
import re
import sys

try:
    import edge_tts
except ImportError:
    print("ERROR: edge-tts package is required.", file=sys.stderr)
    print("Install with: pip install edge-tts", file=sys.stderr)
    sys.exit(1)


def extract_slide_texts(html_path):
    with open(html_path, "r", encoding="utf-8") as f:
        content = f.read()
    m = re.search(r"var\s+slideTexts\s*=\s*\[([\s\S]*?)\]\s*;", content)
    if not m:
        print(f"ERROR: 'var slideTexts = [...]' not found in {html_path}", file=sys.stderr)
        sys.exit(1)
    raw = "[" + m.group(1) + "]"
    texts = re.findall(r'"((?:[^"\\]|\\.)*)"', raw)
    if not texts:
        print(f"ERROR: no strings found in slideTexts array", file=sys.stderr)
        sys.exit(1)
    return texts


async def generate(args):
    texts = extract_slide_texts(args.html)
    os.makedirs(args.output, exist_ok=True)

    total = len(texts)
    pad = max(2, len(str(total)))

    for i, text in enumerate(texts, start=1):
        filename = f"{str(i).zfill(pad)}.mp3"
        filepath = os.path.join(args.output, filename)

        if os.path.exists(filepath) and not args.force:
            size = os.path.getsize(filepath)
            print(f"[{str(i).zfill(pad)}] Skipped: {filename} ({size} bytes)")
            continue

        voice = args.voice
        rate = args.rate
        volume = args.volume

        communicate = edge_tts.Communicate(text, voice, rate=rate, volume=volume)
        print(f"[{str(i).zfill(pad)}] Generating: {filename} ...", end=" ", flush=True)

        await communicate.save(filepath)
        size = os.path.getsize(filepath)
        print(f"OK ({size} bytes)")

    print(f"\nDone. {total} file(s) generated in {args.output}")


def main():
    parser = argparse.ArgumentParser(
        description="Pre-generate MP3 audio for lesson presentations using edge-tts"
    )
    parser.add_argument("html", help="Path to lesson HTML file containing var slideTexts = [...]")
    parser.add_argument("output", help="Output directory for MP3 files")
    parser.add_argument("--voice", default="el-GR-NestorasNeural",
                        help="TTS voice (default: el-GR-NestorasNeural)")
    parser.add_argument("--rate", default="+0%",
                        help="Speaking rate (default: +0%%)")
    parser.add_argument("--volume", default="+0%",
                        help="Speaking volume (default: +0%%)")
    parser.add_argument("--force", action="store_true",
                        help="Overwrite existing MP3 files")
    args = parser.parse_args()

    asyncio.run(generate(args))


if __name__ == "__main__":
    main()
