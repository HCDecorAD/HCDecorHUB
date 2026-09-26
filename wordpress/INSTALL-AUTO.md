# HCDecor one-command installer

## Recommended — Local Site shell
Open Local > HCDecor HUB > **Site shell**.

Place/copy `hcdecor-core` at:
`app/public/wp-content/plugins/hcdecor-core`

Then from the repository `wordpress` folder run:

```bash
bash setup-hcdecor.sh
```

The script:
- verifies WordPress
- installs/activates Hello Elementor
- installs/activates Elementor Free
- activates HCDecor Core
- sets pretty permalinks
- assigns Trang chủ as front page
- configures HCDecor site identity and Elementor defaults
- validates theme, plugins, pages, services and REST endpoint
- prints `HCDecor bootstrap PASS` only after checks complete

It is safe to rerun; existing WordPress content is not deleted.
