#!/usr/bin/env python3
"""Build and serve the editable site for a local client preview."""

from __future__ import annotations

import argparse
from functools import partial
from http.server import SimpleHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path
from typing import Optional, Sequence

if __package__:
    from scripts.build_site import render_site
else:
    from build_site import render_site


PROJECT_ROOT = Path(__file__).resolve().parents[1]
PREVIEW_OUTPUT = PROJECT_ROOT / ".preview-site"


def valid_port(value: str) -> int:
    """Return a valid TCP port or raise an argparse error."""
    try:
        port = int(value)
    except ValueError as error:
        raise argparse.ArgumentTypeError("port must be a number") from error
    if not 1 <= port <= 65535:
        raise argparse.ArgumentTypeError("port must be between 1 and 65535")
    return port


def main(argv: Optional[Sequence[str]] = None) -> int:
    parser = argparse.ArgumentParser(description="Build and preview ThatBioTutor")
    parser.add_argument("--port", type=valid_port, default=8765)
    args = parser.parse_args(argv)

    report = render_site(PROJECT_ROOT, PREVIEW_OUTPUT)
    handler = partial(SimpleHTTPRequestHandler, directory=str(PREVIEW_OUTPUT))
    server = ThreadingHTTPServer(("127.0.0.1", args.port), handler)

    print(f"Built {len(report.pages)} pages and {len(report.aliases)} alias.")
    print(f"Preview: http://127.0.0.1:{args.port}/")
    print("Press Control-C to stop the preview.")
    try:
        server.serve_forever()
    except KeyboardInterrupt:
        print("\nPreview stopped.")
    finally:
        server.server_close()
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
