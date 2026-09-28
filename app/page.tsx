const games = [
  ["Action Arena","Action"],["Drift Legends","Racing"],["Block Puzzle","Puzzle"],
  ["Stickman Clash","Fighting"],["Soccer Stars","Sports"],["Zombie Escape","Adventure"],
  ["Monster Run","Arcade"],["Ninja Jump","Skill"],["Bubble Shooter","Puzzle"],["Moto Rush","Racing"],
  ["Tower Defense","Strategy"],["Fireball.io","Multiplayer"]
];

const categories = ["Action","Adventure","Arcade","Racing","Sports","Puzzle","Shooting","Strategy","Multiplayer","2 Player",".io Games","Skill","Horror","Zombie","Girls & Lifestyle","Educational"];

export default function Home() {
  return (
    <>
      <header className="header">
        <div className="container nav">
          <a className="logo" href="/">GAME<span>ZONE</span></a>
          <nav className="navlinks" aria-label="Main navigation">
            <a href="#popular">Popular</a><a href="#new">New Games</a><a href="#categories">Categories</a>
          </nav>
          <input className="search" aria-label="Search games" placeholder="Search games..." />
        </div>
      </header>

      <main className="container">
        <section className="hero">
          <h1>Free Online Games</h1>
          <p>Play the best free browser games instantly. Discover action, racing, puzzle, sports, multiplayer and more — no download required.</p>
        </section>

        <section className="section" id="popular">
          <div className="sectionHead"><h2>Popular Games</h2><a href="/games">View all</a></div>
          <div className="grid">
            {games.map(([title, cat]) => <a className="card" href={"/game/" + title.toLowerCase().replaceAll(" ","-")} key={title}>
              <div className="thumb">{title}</div><div className="cardBody"><div className="cardTitle">{title}</div><div className="meta">{cat} · Play now</div></div>
            </a>)}
          </div>
        </section>

        <section className="section" id="new">
          <div className="sectionHead"><h2>New Games</h2><a href="/new-games">See all</a></div>
          <div className="grid">
            {games.slice(6).map(([title, cat]) => <a className="card" href={"/game/" + title.toLowerCase().replaceAll(" ","-")} key={title}>
              <div className="thumb">{title}</div><div className="cardBody"><div className="cardTitle">{title}</div><div className="meta">{cat} · New</div></div>
            </a>)}
          </div>
        </section>

        <section className="section" id="categories">
          <div className="sectionHead"><h2>Browse Games by Category</h2><a href="/categories">All categories</a></div>
          <div className="categories">{categories.map(c => <a className="category" href={"/category/" + c.toLowerCase().replaceAll(" ","-")} key={c}>{c} Games</a>)}</div>
        </section>

        <section className="section">
          <h2>Play Free Browser Games</h2>
          <p style={{color:"var(--muted)",lineHeight:1.8,maxWidth:850}}>Find games you can play directly in your browser on desktop, tablet and mobile. Browse by genre, discover new releases, or search for a game and start playing instantly. Our catalog is designed around fast discovery, simple navigation and a focused game-playing experience.</p>
        </section>
      </main>

      <footer className="footer">
        <div className="container">
          <strong>GAMEZONE</strong>
          <div className="footerLinks"><a href="/about">About</a><a href="/contact">Contact</a><a href="/privacy">Privacy</a><a href="/terms">Terms</a><a href="/dmca">DMCA</a></div>
          <p>© 2026 GameZone. Game trademarks belong to their respective owners.</p>
        </div>
      </footer>
    </>
  );
}