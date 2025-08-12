# Docker Cache Corruption Issues on Render

## The Problem
Render's Docker build cache frequently gets corrupted, causing builds to fail with:
```
error: failed to compute cache key: failed commit on ref "layer-sha256:..."
unexpected commit digest sha256:..., expected sha256:...
```

## Root Causes
1. **Render's cache infrastructure** - Their Docker layer caching can become corrupted
2. **Large repository size** (415MB .git) - May contribute to cache issues
3. **Complex multi-stage builds** - More layers = more potential for corruption
4. **Concurrent builds** - Multiple deployments can corrupt shared cache

## Solutions (In Order of Effectiveness)

### 1. Force Cache Invalidation (Quick Fix)
Edit `Dockerfile.render` and increment the `CACHE_BUST` number:
```dockerfile
ARG CACHE_BUST=5  # Increment this number
```

### 2. Simplify Dockerfile
- Remove unnecessary RUN commands
- Combine multiple commands into single layers
- Remove debugging code once issues are resolved
- Avoid heredocs and complex shell operations

### 3. Clear Render's Cache (If Available)
- Check Render dashboard for cache clearing options
- Sometimes redeploying the service helps

### 4. Use --no-cache Flag
If Render supports it, add to build settings:
```
docker build --no-cache
```

## Prevention
1. **Keep Dockerfile simple** - Fewer layers, fewer problems
2. **Regular cache busting** - Increment CACHE_BUST periodically
3. **Monitor build times** - Slow builds may indicate cache issues
4. **Document changes** - Track when cache issues occur

## Quick Recovery Steps
1. Increment `CACHE_BUST` in Dockerfile
2. Commit and push:
   ```bash
   git add brain/Dockerfile.render
   git commit -m "Force cache rebuild - increment CACHE_BUST"
   git push origin main
   ```
3. Monitor deployment logs
4. If still failing, simplify Dockerfile further

## Historical Issues
- Aug 11, 2025: Multiple cache corruptions in one day
- Solution: Simplified Dockerfile, removed debugging code
- Pattern: Happens more frequently with complex Dockerfiles

## Long-term Solutions
1. Consider pre-built images pushed to Docker Hub
2. Use Render's native buildpacks if available
3. Reduce repository size (currently 415MB)
4. Consider alternative deployment platforms if issues persist


