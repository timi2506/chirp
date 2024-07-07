<?php
session_start();

// Check if 'id' parameter is provided in the URL
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);
if (!$id) {
    // Handle error if id parameter is missing or invalid
    echo "User ID not specified or invalid.";
    exit;
}

// Establish a connection to SQLite database
try {
    $db = new PDO('sqlite:' . __DIR__ . '/../chirp.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable error handling
} catch(PDOException $e) {
    // Handle database connection error
    echo "Database connection failed: " . $e->getMessage();
    exit;
}

// Prepare SQL statement to fetch user details based on username (case insensitive)
$stmt = $db->prepare('SELECT * FROM users WHERE LOWER(username) = LOWER(:username)');
$stmt->bindParam(':username', $id);
$stmt->execute();

// Fetch user data
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if user exists
if (!$user) {
    // Handle case where user is not found
    echo "User not found.";
    exit;
}

// Set the page title dynamically
$pageTitle = htmlspecialchars($user['name']) . ' (@' . htmlspecialchars($user['username']) . ') - Chirp';

// Close the database connection
$db = null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="theme-color" content="#00001" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link href="/src/styles/styles.css" rel="stylesheet">
    <link href="/src/styles/timeline.css" rel="stylesheet">
    <link href="/src/styles/menus.css" rel="stylesheet">
    <link href="/src/styles/responsive.css" rel="stylesheet">

    <script defer src="https://cdn.jsdelivr.net/npm/@twemoji/api@latest/dist/twemoji.min.js" crossorigin="anonymous">
    </script>
    <script src="/src/scripts/general.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <title><?php echo $pageTitle; ?></title>
</head>

<body>
    <header>
        <div id="desktopMenu">
            <nav>
                <img src="/src/images/icons/chirp.svg" alt="Chirp" onclick="playChirpSound()">
                <a href="/"><img src="/src/images/icons/house.svg" alt=""> Home</a>
                <a href="/explore"><img src="/src/images/icons/search.svg" alt=""> Explore</a>
                <a href="/notifications"><img src="/src/images/icons/bell.svg" alt=""> Notifications</a>
                <a href="/messages"><img src="/src/images/icons/envelope.svg" alt=""> Messages</a>
                <a
                href="<?php echo isset($_SESSION['username']) ? '/user/?id=' . htmlspecialchars($_SESSION['username']) : '/signin'; ?>"><img
                    src="/src/images/icons/person.svg" alt=""> Profile</a>
                <a href="/compose" class="newchirp">Chirp</a>
            </nav>
            <div id="menuSettings">
                <a href="settings">⚙️ Settings</a>
                <?php if (isset($_SESSION['username'])): ?>
                <a href="/signout.php">🚪 Sign out</a>
                <?php else: ?>
                <a href="/signin/">🚪 Sign in</a>
                <?php endif; ?>
            </div>
            <button id="settingsButtonWrapper" type="button" onclick="showMenuSettings()">
                <img class="userPic"
                    src="<?php echo isset($_SESSION['profile_pic']) ? htmlspecialchars($_SESSION['profile_pic']) : '/src/images/users/guest/user.svg'; ?>"
                    alt="<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'guest'; ?>">
                <div>
                    <p class="usernameMenu">
                        <?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Guest'; ?>
                        <?php if (isset($_SESSION['is_verified']) && $_SESSION['is_verified']): ?>
                        <img class="emoji" src="/src/images/icons/verified.svg" alt="Verified">
                        <?php endif; ?>
                    </p>
                    <p class="subText">
                        @<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'guest'; ?>
                    </p>
                </div>
                <p class="settingsButton">⚙️</p>
            </button>
        </div>
    </header>
    <main>
        <div id="feed">
            <div id="iconChirp" onclick="playChirpSound()">
                <img src="/src/images/icons/chirp.svg" alt="Chirp">
            </div>
            <div id="timelineSelect">
                <button id="back" class="selcted" onclick="back()"><img alt="" class="emoji"
                        src="/src/images/icons/back.svg"> Back</button>
            </div>
            <div id="chirps">
                <img class="userBanner"
                    src="<?php echo isset($user['profilePic']) ? htmlspecialchars($user['userBanner']) : '/src/images/users/chirp/banner.png'; ?>">
                <div class="account">
                    <div class="accountInfo">
                        <div>
                            <img class="userPic"
                                src="<?php echo isset($user['profilePic']) ? htmlspecialchars($user['profilePic']) : '/src/images/users/guest/user.svg'; ?>"
                                alt="<?php echo htmlspecialchars($user['name']); ?>">
                            <div>
                                <p><?php echo htmlspecialchars($user['name']); ?></p>
                                <p class="subText">@<?php echo htmlspecialchars($user['username']); ?></p>
                            </div>
                        </div>
                        <div class="timestampTimeline">
                            <!-- Edit profile button or other actions -->
                            <a class="followButton">Edit profile</a>
                        </div>
                    </div>
                    <p>
                        <?php echo isset($user['bio']) ? htmlspecialchars($user['bio']) : 'This is a bio where you describe your account using at most 120 characters.'; ?>
                    </p>
                    <div id="accountStats">
                        <p class="subText">
                            <?php echo isset($user['following']) ? htmlspecialchars($user['following']) . ' following' : '0 following'; ?>
                        </p>
                        <p class="subText">
                            <?php echo isset($user['followers']) ? htmlspecialchars($user['followers']) . ' followers' : '0 followers'; ?>
                        </p>
                    </div>
                </div>
                <div id="userNav">
                    <a id="chirpsNav" class="selcted">Chirps</a>
                    <a id="repliesNav">Replies</a>
                    <a id="mediaNav">Media</a>
                    <a id="likesNav">Likes</a>
                </div>
            </div>
            <div id="posts" data-offset="0">
                <!-- Chirps will be loaded here -->
            </div>
            <div id="noMoreChirps">
                <div class="lds-ring">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
            </div>
        </div>
    </main>
    <aside id="sideBar">
        <div id="trends">
            <p>Trends</p>
            <div>
                <a>gay people</a>
                <p class="subText">12 chirps</p>
            </div>
            <div>
                <a>twitter</a>
                <p class="subText">47 chirps</p>
            </div>
            <div>
                <a>iphone 69</a>
                <p class="subText">62 chirps</p>
            </div>
        </div>
        <div id="whotfollow">
            <p>Suggested accounts</p>
            <div>
                <div>
                    <img class="userPic"
                        src="https://pbs.twimg.com/profile_images/1797665112440045568/305XgPDq_400x400.png" alt="Apple">
                    <div>
                        <p>Apple <img class="verified" src="/src/images/icons/verified.svg" alt="Verified"></p>
                        <p class="subText">@apple</p>
                    </div>
                </div>
                <a class="followButton following">Following</a>
            </div>
            <div>
                <div>
                    <img class="userPic"
                        src="https://pbs.twimg.com/profile_images/1380530524779859970/TfwVAbyX_400x400.jpg"
                        alt="President Biden">
                    <div>
                        <p>President Biden <img class="verified" src="/src/images/icons/verified.svg" alt="Verified">
                        </p>
                        <p class="subText">@POTUS</p>
                    </div>
                </div>
                <a class="followButton">Follow</a>
            </div>
        </div>
        </div>
        <div>
            <p class="subText">Inspired by Twitter/X. No code has been sourced from Twitter/X. Twemoji by Twitter Inc/X
                Corp is licensed under CC-BY 4.0.

                <br><br>You're running: Chirp Beta 0.0.4b
            </p>
        </div>
    </aside>
    <footer>
        <div class="mobileCompose">
            <a class="chirpMoile" href="compose">Chirp</a>
        </div>
        <div>
            <a href="/"><img src="/src/images/icons/house.svg" alt="Home"></a>
            <a href="/explore"><img src="/src/images/icons/search.svg" alt="Explore"></a>
            <a href="/notifications"><img src="/src/images/icons/bell.svg" alt="Notifications"></a>
            <a href="/messages"><img src="/src/images/icons/envelope.svg" alt="Messages"></a>
            <a
                href="<?php echo isset($_SESSION['username']) ? '/user/?id=' . htmlspecialchars($_SESSION['username']) : '/signin'; ?>"><img
                    src="/src/images/icons/person.svg" alt="Profile"></a>
        </div>
    </footer>
    <script>
    let loadingChirps = false; // Flag to track if chirps are currently being loaded

    function updatePostedDates() {
        const chirps = document.querySelectorAll('.chirp .postedDate');
        chirps.forEach(function(chirp) {
            const timestamp = chirp.getAttribute('data-timestamp');
            const postDate = new Date(parseInt(timestamp) * 1000);
            const now = new Date();
            const diffInMilliseconds = now - postDate;
            const diffInSeconds = Math.floor(diffInMilliseconds / 1000);
            const diffInMinutes = Math.floor(diffInSeconds / 60);
            const diffInHours = Math.floor(diffInMinutes / 60);
            const diffInDays = Math.floor(diffInHours / 24);

            let relativeTime;

            if (diffInSeconds < 60) {
                relativeTime = diffInSeconds + "s ago";
            } else if (diffInMinutes < 60) {
                relativeTime = diffInMinutes + "m ago";
            } else if (diffInHours < 24) {
                relativeTime = diffInHours + "h ago";
            } else if (diffInDays < 7) {
                relativeTime = diffInDays + "d ago";
            } else {
                const options = {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit'
                };
                relativeTime = postDate.toLocaleString([], options);
            }

            chirp.textContent = relativeTime;
        });
    }

    function showLoadingSpinner() {
        document.getElementById('noMoreChirps').style.display = 'block';
    }

    function hideLoadingSpinner() {
        document.getElementById('noMoreChirps').style.display = 'none';
    }

    function loadChirps() {
    if (loadingChirps) return; // If already loading, exit

    const chirpsContainer = document.getElementById('posts');
    const offset = parseInt(chirpsContainer.getAttribute('data-offset'));

    loadingChirps = true; // Set loading flag
    showLoadingSpinner(); // Show loading spinner

    setTimeout(() => {
        fetch(`/user/fetch_chirps.php?offset=${offset}&user=<?php echo $user['id']; ?>`)
            .then(response => response.json())
            .then(chirps => {
                chirps.forEach(chirp => {
                    const chirpDiv = document.createElement('div');
                    chirpDiv.className = 'chirp';
                    chirpDiv.id = chirp.id;
                    chirpDiv.innerHTML = `
                        <a class="chirpClicker" href="/chirp/?id=${chirp.id}">
                            <div class="chirpInfo">
                                <div>
                                    <img class="userPic"
                                        src="${chirp.profilePic ? chirp.profilePic : '/src/images/users/guest/user.svg'}"
                                        alt="${chirp.name ? chirp.name : 'Guest'}">
                                    <div>
                                        <p>${chirp.name ? chirp.name : 'Guest'}
                                            ${chirp.isVerified ? '<img class="verified" src="/src/images/icons/verified.svg" alt="Verified">' : ''}
                                        </p>
                                        <p class="subText">@${chirp.username ? chirp.username : 'guest'}</p>
                                    </div>
                                </div>
                                <div class="timestampTimeline">
                                    <p class="subText postedDate" data-timestamp="${chirp.timestamp}"></p>
                                </div>
                            </div>
                            <pre>${chirp.chirp}</pre>
                        </a>
                        <div class="chirpInteract">
                            <button type="button" class="reply"><img alt="Reply" src="/src/images/icons/reply.svg"> ${chirp.reply_count}</button>
                            <a href="/chirp/?id=${chirp.id}"></a>
                            <button type="button" class="rechirp" onClick="updateChirpInteraction(${chirp.id}, 'rechirp', this)"><img alt="Rechirp" src="/src/images/icons/${chirp.rechirped_by_current_user ? 'rechirped' : 'rechirp'}.svg"> ${chirp.rechirp_count}</button>
                            <a href="/chirp/?id=${chirp.id}"></a>
                            <button type="button" class="like" onClick="updateChirpInteraction(${chirp.id}, 'like', this)"><img alt="Like" src="/src/images/icons/${chirp.liked_by_current_user ? 'liked' : 'like'}.svg"> ${chirp.like_count}</button>
                        </div>
                    `;
                    chirpsContainer.appendChild(chirpDiv);
                });

                chirpsContainer.setAttribute('data-offset', offset + 12);

                updatePostedDates();
                twemoji.parse(chirpsContainer);
            })
            .catch(error => {
                console.error('Error fetching chirps:', error);
            })
            .finally(() => {
                loadingChirps = false; // Reset loading flag
                hideLoadingSpinner(); // Hide loading spinner
            });
    }, 450);
}

function updateChirpInteraction(chirpId, action, button) {
    fetch(`/interact_chirp.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ chirpId, action })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const countElement = button.querySelector('span');
            const currentCount = parseInt(countElement.textContent);
            if (action === 'like') {
                button.querySelector('img').src = data.liked ? '/src/images/icons/liked.svg' : '/src/images/icons/like.svg';
                countElement.textContent = data.like_count;
            } else if (action === 'rechirp') {
                button.querySelector('img').src = data.rechirped ? '/src/images/icons/rechirped.svg' : '/src/images/icons/rechirp.svg';
                countElement.textContent = data.rechirp_count;
            }
        } else if (data.error === 'not_signed_in') {
            window.location.href = '/signin/';
        }
    })
    .catch(error => {
        console.error('Error updating interaction:', error);
    });
}

loadChirps();

window.addEventListener('scroll', () => {
    if (window.innerHeight + window.scrollY >= document.body.offsetHeight) {
        loadChirps();
    }
});

setInterval(updatePostedDates, 1000);

<?php
if (isset($_SESSION['error_message'])) {
    echo 'console.error(' . json_encode($_SESSION['error_message']) . ');';
    unset($_SESSION['error_message']); // Clear the error message after displaying it
}
?>


    </script>

</body>

</html>