# ffmpeg

convert an video to gif image:

```bash
ffmpeg -i 1.avi -ss 00:00:00 -t 00:00:10 -async 1 -vf "fps=30,scale=1600👎flags=lanczos,split[s0][s1];[s0]palettegen[p];[s1][p]paletteuse" -loop 1 customization.gif
```

