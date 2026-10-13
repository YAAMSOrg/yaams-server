#!/usr/bin/env bash
#
# Build a YAAMS dev snapshot image locally from a git ref, using the
# production Dockerfile (Docker/Dockerfile.prod).
#
# The build context is a `git archive` of the chosen commit, so only committed
# files reach the image - the working tree is never touched, and local edits,
# untracked files or a stray .env cannot leak in.
#
# Produces two tags:
#   <image>:dev            - always points at the latest snapshot
#   <image>:dev-<sha>      - immutable, for rollbacks
#
# Older dev-<sha> tags beyond --keep are removed afterwards.

set -euo pipefail

IMAGE="yaams-server"
REF="main"
REMOTE="origin"
KEEP=5
FETCH=1

usage() {
    cat <<EOF
Usage: $(basename "$0") [options]

Options:
  -r, --ref REF       Branch, tag or commit to build (default: ${REF})
  -i, --image NAME    Image name, without tag (default: ${IMAGE})
  -k, --keep N        Number of dev-<sha> snapshots to keep, 0 = keep all (default: ${KEEP})
      --remote NAME   Git remote to fetch from (default: ${REMOTE})
      --no-fetch      Build from local refs, skip 'git fetch'
  -h, --help          Show this help
EOF
}

die() {
    echo "Error: $*" >&2
    exit 1
}

log() {
    echo "==> $*"
}

while [[ $# -gt 0 ]]; do
    case "$1" in
        -r|--ref)    REF="${2:?missing value for $1}"; shift 2 ;;
        -i|--image)  IMAGE="${2:?missing value for $1}"; shift 2 ;;
        -k|--keep)   KEEP="${2:?missing value for $1}"; shift 2 ;;
        --remote)    REMOTE="${2:?missing value for $1}"; shift 2 ;;
        --no-fetch)  FETCH=0; shift ;;
        -h|--help)   usage; exit 0 ;;
        *)           usage >&2; die "unknown option: $1" ;;
    esac
done

[[ "$KEEP" =~ ^[0-9]+$ ]] || die "--keep must be a non-negative integer"

command -v git >/dev/null || die "git is not installed"
command -v docker >/dev/null || die "docker is not installed"
docker info >/dev/null 2>&1 || die "cannot reach the Docker daemon"

# Run from the repository this script lives in, regardless of the caller's cwd.
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(git -C "$SCRIPT_DIR" rev-parse --show-toplevel)" \
    || die "script is not inside a git repository"
cd "$REPO_ROOT"

if [[ "$FETCH" -eq 1 ]]; then
    log "Fetching ${REMOTE}"
    git fetch --tags --prune "$REMOTE"
fi

# Prefer the remote-tracking branch so a stale local branch is never built.
if [[ "$FETCH" -eq 1 ]] && git rev-parse --verify --quiet "refs/remotes/${REMOTE}/${REF}^{commit}" >/dev/null; then
    SHA="$(git rev-parse "refs/remotes/${REMOTE}/${REF}^{commit}")"
else
    SHA="$(git rev-parse --verify --quiet "${REF}^{commit}")" \
        || die "cannot resolve ref '${REF}'"
fi
SHORT_SHA="$(git rev-parse --short "$SHA")"

VERSION="$(git show "${SHA}:config/app.php" \
    | sed -nE "s/.*'version'[[:space:]]*=>[[:space:]]*'([^']+)'.*/\1/p" \
    | head -n1)"
VERSION="${VERSION:-unknown}"

SNAPSHOT_TAG="dev-${SHORT_SHA}"

log "Building ${IMAGE}:${SNAPSHOT_TAG} (ref: ${REF}, commit: ${SHA}, version: ${VERSION})"

git archive --format=tar "$SHA" \
    | docker build \
        --file Docker/Dockerfile.prod \
        --tag "${IMAGE}:${SNAPSHOT_TAG}" \
        --tag "${IMAGE}:dev" \
        --label "org.opencontainers.image.revision=${SHA}" \
        --label "org.opencontainers.image.version=${VERSION}-dev+${SHORT_SHA}" \
        --label "org.opencontainers.image.created=$(date -u +%Y-%m-%dT%H:%M:%SZ)" \
        --label "org.opencontainers.image.ref.name=${REF}" \
        -

log "Built ${IMAGE}:dev and ${IMAGE}:${SNAPSHOT_TAG}"

if [[ "$KEEP" -gt 0 ]]; then
    # Newest first; CreatedAt is "YYYY-MM-DD HH:MM:SS ..." so it sorts lexically.
    # The current snapshot is excluded, so KEEP-1 older ones survive.
    while IFS= read -r tag; do
        [[ -n "$tag" ]] || continue
        log "Removing old snapshot ${IMAGE}:${tag}"
        docker image rm "${IMAGE}:${tag}" >/dev/null \
            || echo "Warning: could not remove ${IMAGE}:${tag} (still in use?)" >&2
    done < <(
        docker image ls "$IMAGE" --format '{{.CreatedAt}}|{{.Tag}}' \
            | grep -E '\|dev-[0-9a-f]+$' \
            | sort -r \
            | cut -d'|' -f2 \
            | grep -vx "$SNAPSHOT_TAG" \
            | tail -n +"$KEEP"
    )
fi

cat <<EOF

Done. To run it, point your compose services at the snapshot:

  image: ${IMAGE}:dev
  pull_policy: never

then: docker compose up -d
EOF
