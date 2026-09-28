const games = ["Action Arena","Drift Legends","Block Puzzle","Stickman Clash","Soccer Stars","Zombie Escape","Monster Run","Ninja Jump","Bubble Shooter","Moto Rush","Tower Defense","Fireball.io","Speed Racer","Pixel Adventure","Basket Master","Ninja Survival","Word Challenge","Tank Battle"];

export default function GamesPage(){
  return <main className="container" style={{padding:"42px 0 70px"}}>
    <a href="/" style={{color:"var(--muted)",fontSize:13}}>← Home</a>
    <h1 style={{fontSize:"clamp(30px,4vw,48px)",margin:"22px 0 24px"}}>All Free Online Games</h1>
    <div className="grid">{games.map(title=><a className="card" href={"/game/"+title.toLowerCase().replaceAll(" ","-")} key={title}><div className="thumb">{title}</div><div className="cardBody"><div className="cardTitle">{title}</div><div className="meta">Play online · Free</div></div></a>)}</div>
  </main>
}