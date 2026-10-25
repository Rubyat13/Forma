# Plugin directory art

Source for the WordPress.org icon and banner in `.wordpress-org/`. Both are HTML pages drawn with the Forma 3D theme's colours and fonts and the demo product images in `demo/images/`, then captured frame by frame with Playwright and joined with ffmpeg.

- `banner.html`: 772×250 banner, a 6 s loop. The lounge chair turns through its three demo angles, then the cursor clicks "Import design".
- `icon.html`: 128×128 icon, a 3.2 s loop where the Forma mark turns half a turn, like the logo hover in the theme. `?v=light` is the one in use; `?v=dark` and `?v=cards` are the other options.

## Rebuild

Needs Node, ffmpeg, and `npm install playwright-core && npx playwright install chromium`. Run from this folder:

```sh
# Banner: animated PNG at both sizes
node frames.js banner.html frames/b2x 772 250 2 20 6
node frames.js banner.html frames/b1x 772 250 1 20 6
ffmpeg -y -framerate 20 -i frames/b2x/%04d.png -plays 0 -f apng ../banner-1544x500.png
ffmpeg -y -framerate 20 -i frames/b1x/%04d.png -plays 0 -f apng ../banner-772x250.png

# Icon: animated GIF at both sizes
node frames.js "icon.html?v=light" frames/icon 128 128 2 25 3.2
for s in 256 128; do
  ffmpeg -y -framerate 25 -i frames/icon/%04d.png -filter_complex "scale=${s}:${s}:flags=lanczos,split[a][b];[a]palettegen=max_colors=256:stats_mode=full[p];[b][p]paletteuse=dither=sierra2_4a" -loop 0 ../icon-${s}x${s}.gif
done

# A still of either page
node render.js banner.html banner.png 772 250 2
```

Copy the results into the SVN `assets/` folder and set `svn:mime-type` (`image/png`, `image/gif`) before committing.

Fonts: Instrument Serif and Inter Tight, SIL Open Font License (see `fonts/`). Demo images: Poly Haven models, CC0.
