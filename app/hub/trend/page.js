import {HubShell,PageCards} from "../../../components/HubShell";

const modules=[
["TREND_RADAR","Trend Radar","Scan fast-moving signals across supported sources. Start here to see what is rising now.","https://support.google.com/trends/answer/3076011?hl=en"],
["EARLY_SIGNAL","Early Signals","Prioritize acceleration and freshness before a topic becomes saturated.","https://support.google.com/trends/answer/3076011?hl=en"],
["TREND_SCORE","Opportunity Score","Rank opportunities by freshness, growth, audience fit, competition, production speed and monetization fit."],
["TREND_GAP","Content Gap","Find demand where viewers still lack relevant or high-quality content.","https://support.google.com/youtube/answer/11962757?co=GENIE.Platform%3DDesktop&hl=en"],
["MONEY_PATH","Monetization Path","Define how attention can become business value before spending production effort."],
["AI_ANGLE","AI Content Angles","Turn one opportunity into hooks, formats, viewpoints and platform-specific concepts."],
["CONTENT_FACTORY","Content Factory","Convert a winning idea into multiple hooks, scripts, Shorts/Reels, long-form, carousel and captions."],
["FAST_TEST","Fast Test","Publish small controlled tests quickly instead of mass-producing an unproven idea."],
["WINNER_DETECT","Winner Detection","Compare real performance with the account and format baseline; identify candidates to scale.","https://support.google.com/youtube/answer/12942217?co=YOUTUBE._YTVideoType%3Dvideo&hl=en"],
["WINNER_LOOP","Winner Loop","Learn which hook, format, topic and account won; generate the next evidence-based variants."],
["SCALE","Scale Winners","Increase output only after evidence shows a repeatable winner."],
["TIKTOK_TRENDS","TikTok Trends Guide","Use Creative Center trendline, related videos, audience, region and related hashtags as research evidence.","https://ads.tiktok.com/resources/help/article/how-to-use-trends?lang=en-GB"]
];

export default function TrendMonetization(){
 return <HubShell title="Trend & Monetization" eyebrow="HCDECOR HUB / OPPORTUNITY ENGINE">
   <div className="notice"><b>Operating loop:</b> SCAN → EARLY SIGNAL → SCORE → CONTENT GAP → MONEY FIT → AI ANGLE → PRODUCE → TEST → WINNER → SCALE. Guide links explain the evidence source; they do not imply an API is connected.</div>
   <PageCards items={modules}/>
   <section className="masterHero">
    <div><span className="masterBadge">DECISION RULE</span><h2>Learn fast. Scale winners.</h2><p>Do not optimize for post count first. Optimize the speed of learning: test small, verify real performance, stop weak ideas, and multiply evidence-backed winners.</p></div>
    <div className="masterFlow">
     <div><b>01</b><span>🔥 DO NOW — rising + strong fit</span></div>
     <div><b>02</b><span>🟢 DO — audience fit is strong</span></div>
     <div><b>03</b><span>🟡 TEST 3 — evidence incomplete</span></div>
     <div><b>04</b><span>🔴 SKIP — weak money path / late trend</span></div>
    </div>
   </section>
   <div className="notice"><b>Handoff:</b> Trend winner → Content Factory → Social / Publishing → Verify → Analytics → Winner Loop → Scale. Existing Social/Publishing engines remain the execution path; this workspace does not duplicate them.</div>
 </HubShell>
}
