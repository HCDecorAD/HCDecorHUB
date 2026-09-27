import { readWordPressSite, wordpressWriteCapability } from "../../../../lib/cms/wordpress";

export async function GET() {
  try {
    const site = await readWordPressSite();
    return Response.json({
      provider: "wordpress.com",
      read: site,
      write: wordpressWriteCapability(),
    }, { status: site.ok ? 200 : 502 });
  } catch {
    return Response.json({ provider: "wordpress.com", status: "error" }, { status: 502 });
  }
}
