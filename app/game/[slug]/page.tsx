import type { Metadata } from "next";
type Props = { params: Promise<{ slug: string }> };
function titleFromSlug(slug:string){ return slug.split("-").map(x => x.charAt(0).toUpperCase()+x.slice(1)).join(" "); }
export async function generateMetadata({params}: Props): Promise<Metadata> {
 const {slug}=await params; const title=titleFromSlug(slug);
 return {title: title+" - Play Online Free",description:"Play "+title+" online in your browser. Game information, controls, category and similar games.",robots:{index:false,follow:true},alternates:{canonical:"/game/"+slug}};
}
export default async function GamePage({params}: Props) {
 const {slug}=await params; const title=titleFromSlug(slug);
 const similar=["Action Arena","Drift Legends","Block Puzzle","Monster Run","Ninja Jump","Moto Rush"];
 return <main className="container gamePage">
  <nav className="breadcrumbs" aria-label="Breadcrumb"><a href="/">Home</a><span>›</span><span>{title}</span></nav>
  <div className="gameHeader"><div><div className="kicker">BROWSER GAME</div><h1>{title}</h1><p>Play {title} online for free on desktop, tablet and mobile.</p></div><a className="primaryBtn" href="#play">Play now</a></div>
  <div id="play" className="playerBox"><div className="playerMessage"><strong>Game player</strong><span>GameMonetize catalog integration is the next step.</span></div></div>
  <div className="gameColumns"><section className="gameContent"><h2>About {title}</h2><p>This page is prepared for the live catalog. Once the GameMonetize feed is connected, the official title, description, thumbnail, category, tags and playable URL will load automatically.</p><h2>How to Play</h2><p>Controls and instructions will be displayed from the catalog when available. The player will be optimized for browser play on desktop and mobile devices.</p></section>
  <aside className="gameInfo"><div><span>Category</span><a href="/categories">Browse games</a></div><div><span>Platform</span><b>Web browser</b></div><div><span>Devices</span><b>Desktop · Mobile · Tablet</b></div><div><span>Provider</span><b>GameMonetize</b></div></aside></div>
  <section className="section"><div className="sectionHead"><div><div className="kicker">KEEP PLAYING</div><h2>More Games</h2></div><a href="/games">View all →</a></div><div className="grid">{similar.map(game=><a className="card" href={"/game/"+game.toLowerCase().replaceAll(" ","-")} key={game}><div className="thumb">{game}</div><div className="cardBody"><div className="cardTitle">{game}</div><div className="meta">Play online · Free</div></div></a>)}</div></section>
 </main>;
}