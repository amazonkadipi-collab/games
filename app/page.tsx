import Image from "next/image";
import { getCatalog, type Game } from "@/lib/catalog";

const fallbackPopular = [
  ["Action Arena","Action"],["Drift Legends","Racing"],["Block Puzzle","Puzzle"],["Stickman Clash","Fighting"],
  ["Soccer Stars","Sports"],["Zombie Escape","Adventure"],["Monster Run","Arcade"],["Ninja Jump","Skill"]
];
const fallbackNew = [["Speed Racer","Racing"],["Pixel Adventure","Adventure"],["Basket Master","Sports"],["Ninja Survival","Survival"],["Word Challenge","Puzzle"],["Tank Battle","Shooting"]];
const categories = [["Action","action"],["Adventure","adventure"],["Arcade","arcade"],["Racing","racing"],["Sports","sports"],["Puzzle","puzzle"],["Shooting","shooting"],["Strategy","strategy"],["Multiplayer","multiplayer"],["2 Player","2-player"],[".io Games","io-games"],["Skill","skill"],["Horror","horror"],["Zombie","zombie"],["Driving","driving"],["Fighting","fighting"],["Simulation","simulation"],["Idle","idle"],["Platform","platform"],["Stickman","stickman"]];
const tags = ["3D","1 Player","2 Player","Battle Royale","Car","Drift","Football","Horror","Obby","Parkour","Ragdoll","Sniper","Stickman","Survival","Tower Defense","Zombie"];

function DemoCard({title,category,label}:{title:string;category:string;label?:string}) {
  return <a className="card" href={`/game/${title.toLowerCase().replaceAll(" ","-")}`}><div className="thumb"><span>{title}</span></div><div className="cardBody"><div className="cardTitle">{title}</div><div className="meta">{category} · {label || "Play now"}</div></div></a>;
}
function LiveCard({game}:{game:Game}) {
  return <a className="card" href={`/game/${game.slug}`}><div className="thumb">{game.thumbnail ? <Image src={game.thumbnail} alt={game.title} width={640} height={360} loading="lazy" /> : <span>{game.title}</span>}</div><div className="cardBody"><div className="cardTitle">{game.title}</div><div className="meta">{game.category || "Online Game"} · Play now</div></div></a>;
}

export default async function Home() {
  const catalog = await getCatalog();
  const live = catalog.length > 0;
  const popular = catalog.slice(0,12);
  const newest = [...catalog].sort((a,b)=>(b.publishedAt||"").localeCompare(a.publishedAt||"")).slice(0,8);
  return <>
    <header className="header"><div className="container nav"><a className="logo" href="/">GAME<span>ZONE</span></a><nav className="navlinks" aria-label="Main navigation"><a href="#popular">Popular</a><a href="#new">New</a><a href="/categories">Categories</a></nav><form className="searchWrap" action="/search"><input className="search" name="q" aria-label="Search games" placeholder="Search games..." /></form></div></header>
    <main className="container">
      <section className="hero"><div className="eyebrow">PLAY INSTANTLY · DESKTOP · MOBILE</div><h1>Free Online Games</h1><p>Play browser games instantly. Discover popular games, new releases, multiplayer games and hundreds of genres without downloads.</p><div className="heroActions"><a className="primaryBtn" href="/games">Explore all games</a><a className="secondaryBtn" href="/categories">Browse categories</a></div></section>
      <section className="section" id="popular"><div className="sectionHead"><div><div className="kicker">{live ? "LIVE CATALOG" : "TRENDING NOW"}</div><h2>Popular Games</h2></div><a href="/games">View all →</a></div><div className="grid">{live ? popular.map(g=><LiveCard game={g} key={g.id||g.slug}/>) : fallbackPopular.map(([t,c])=><DemoCard title={t} category={c} key={t}/>)}</div></section>
      <section className="section" id="new"><div className="sectionHead"><div><div className="kicker">{live ? "LATEST IN FEED" : "JUST ADDED"}</div><h2>New Games</h2></div><a href="/games">See all →</a></div><div className="grid">{live ? newest.map(g=><LiveCard game={g} key={g.id||g.slug}/>) : fallbackNew.map(([t,c])=><DemoCard title={t} category={c} label="New" key={t}/>)}</div></section>
      <section className="section"><div className="sectionHead"><div><div className="kicker">DISCOVER</div><h2>Browse by Category</h2></div><a href="/categories">All categories →</a></div><div className="categories">{categories.map(([name,slug])=><a className="category" href={`/category/${slug}`} key={slug}>{name} Games</a>)}</div></section>
      <section className="section"><div className="sectionHead"><div><div className="kicker">QUICK FILTERS</div><h2>Popular Tags</h2></div></div><div className="tags">{tags.map(tag=><a href={`/search?q=${encodeURIComponent(tag)}`} key={tag}>#{tag}</a>)}</div></section>
      <section className="seoIntro"><h2>Play Free Browser Games Online</h2><p>GameZone is built for fast game discovery: browse by genre, search by title, explore popular games and find new browser games on desktop, tablet and mobile. {live ? "The live catalog powers game discovery and individual game pages." : "Connect the GameMonetize catalog to populate the site with real games automatically."}</p></section>
    </main>
    <footer className="footer"><div className="container"><div className="footerTop"><strong className="logo">GAME<span>ZONE</span></strong><span>Free browser games, built for fast discovery.</span></div><div className="footerLinks"><a href="/about">About</a><a href="/contact">Contact</a><a href="/privacy">Privacy</a><a href="/terms">Terms</a><a href="/dmca">DMCA</a></div><p>© 2026 GameZone. Game names, trademarks and content belong to their respective owners.</p></div></footer>
  </>;
}
