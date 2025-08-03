@extends('game.layout')

@section('title', 'Accessibility Features')
@section('meta_description', 'Learn about the accessibility features in Grassland Awakening and how to customize your gaming experience.')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h1 class="card-title h3 mb-0">
                    <span aria-hidden="true">♿</span> Accessibility Features
                </h1>
            </div>
            <div class="card-body">
                <div class="alert alert-info" role="region" aria-labelledby="intro-heading">
                    <h2 id="intro-heading" class="alert-heading h5">
                        <span aria-hidden="true">💡</span> Welcome to Accessible Gaming
                    </h2>
                    <p class="mb-0">
                        Grassland Awakening is designed to be playable by everyone. This page outlines the accessibility features 
                        built into the game and provides tips for the best experience.
                    </p>
                </div>

                <!-- Screen Reader Support -->
                <section aria-labelledby="screen-reader-heading" class="mb-5">
                    <h2 id="screen-reader-heading" class="h4 mb-3">
                        <span aria-hidden="true">🔊</span> Screen Reader Support
                    </h2>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title h6">ARIA Labels & Descriptions</h3>
                                    <p class="card-text small">
                                        All interactive elements have descriptive labels. Complex UI components 
                                        include detailed descriptions to explain their purpose and current state.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title h6">Live Regions</h3>
                                    <p class="card-text small">
                                        Important game updates, combat results, and status changes are announced 
                                        automatically to screen readers using live regions.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title h6">Semantic HTML</h3>
                                    <p class="card-text small">
                                        All content uses proper HTML structure with headings, landmarks, and 
                                        semantic elements for easy navigation.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title h6">Alternative Text</h3>
                                    <p class="card-text small">
                                        All images, icons, and visual elements have descriptive alternative text 
                                        or are marked as decorative when appropriate.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Keyboard Navigation -->
                <section aria-labelledby="keyboard-heading" class="mb-5">
                    <h2 id="keyboard-heading" class="h4 mb-3">
                        <span aria-hidden="true">⌨️</span> Keyboard Navigation
                    </h2>
                    <div class="table-responsive">
                        <table class="table table-striped" role="table">
                            <caption class="sr-only">Keyboard shortcuts for navigating Grassland Awakening</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Shortcut</th>
                                    <th scope="col">Action</th>
                                    <th scope="col">Available On</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th scope="row"><kbd>Tab</kbd> / <kbd>Shift+Tab</kbd></th>
                                    <td>Navigate between interactive elements</td>
                                    <td>All pages</td>
                                </tr>
                                <tr>
                                    <th scope="row"><kbd>Enter</kbd> / <kbd>Space</kbd></th>
                                    <td>Activate buttons and links</td>
                                    <td>All pages</td>
                                </tr>
                                <tr>
                                    <th scope="row"><kbd>Escape</kbd></th>
                                    <td>Close modals or return to main content</td>
                                    <td>All pages</td>
                                </tr>
                                <tr>
                                    <th scope="row"><kbd>Alt+H</kbd></th>
                                    <td>Go to Dashboard</td>
                                    <td>All pages</td>
                                </tr>
                                <tr>
                                    <th scope="row"><kbd>Alt+V</kbd></th>
                                    <td>Go to Village</td>
                                    <td>All pages</td>
                                </tr>
                                <tr>
                                    <th scope="row"><kbd>Alt+A</kbd></th>
                                    <td>Go to Adventures</td>
                                    <td>All pages</td>
                                </tr>
                                <tr>
                                    <th scope="row"><kbd>Arrow Keys</kbd></th>
                                    <td>Navigate within menus and lists</td>
                                    <td>Dropdown menus, lists</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Visual Accessibility -->
                <section aria-labelledby="visual-heading" class="mb-5">
                    <h2 id="visual-heading" class="h4 mb-3">
                        <span aria-hidden="true">👁️</span> Visual Accessibility
                    </h2>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title h6">High Contrast Support</h3>
                                    <p class="card-text small">
                                        The game automatically adapts to your system's high contrast preferences, 
                                        ensuring maximum readability.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title h6">Dark Mode Support</h3>
                                    <p class="card-text small">
                                        Colors automatically adjust based on your system's dark mode preference 
                                        for comfortable viewing in any lighting condition.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title h6">Focus Indicators</h3>
                                    <p class="card-text small">
                                        All interactive elements have clear, visible focus indicators when navigating 
                                        with the keyboard.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title h6">Large Click Targets</h3>
                                    <p class="card-text small">
                                        All buttons and interactive elements meet minimum size requirements (44x44 pixels) 
                                        for easy clicking and tapping.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Color & Contrast -->
                <section aria-labelledby="color-heading" class="mb-5">
                    <h2 id="color-heading" class="h4 mb-3">
                        <span aria-hidden="true">🎨</span> Color & Information Design
                    </h2>
                    <div class="alert alert-success" role="region">
                        <h3 class="alert-heading h6">Color-Blind Friendly</h3>
                        <ul class="mb-0">
                            <li>Information is never conveyed by color alone</li>
                            <li>Status indicators use both color and symbols/text</li>
                            <li>Progress bars include patterns and labels</li>
                            <li>All color combinations meet WCAG contrast requirements</li>
                        </ul>
                    </div>
                </section>

                <!-- Motor Accessibility -->
                <section aria-labelledby="motor-heading" class="mb-5">
                    <h2 id="motor-heading" class="h4 mb-3">
                        <span aria-hidden="true">🤲</span> Motor Accessibility
                    </h2>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title h6">Reduced Motion</h3>
                                    <p class="card-text small">
                                        Respects your system's "prefers-reduced-motion" setting to minimize 
                                        animations that could cause discomfort.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title h6">No Time Pressure</h3>
                                    <p class="card-text small">
                                        Turn-based combat and exploration means you can take as much time as 
                                        needed to make decisions.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Cognitive Accessibility -->
                <section aria-labelledby="cognitive-heading" class="mb-5">
                    <h2 id="cognitive-heading" class="h4 mb-3">
                        <span aria-hidden="true">🧠</span> Cognitive Accessibility
                    </h2>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title h6">Clear Interface</h3>
                                    <p class="card-text small">
                                        Simple, consistent layout with clear headings and logical navigation structure.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title h6">Progress Tracking</h3>
                                    <p class="card-text small">
                                        Clear indicators of progress, achievements, and next steps help maintain orientation.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title h6">Helpful Feedback</h3>
                                    <p class="card-text small">
                                        Success and error messages are clear and provide guidance for next actions.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title h6">Consistent Patterns</h3>
                                    <p class="card-text small">
                                        Similar functions work the same way across different parts of the game.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Technical Requirements -->
                <section aria-labelledby="technical-heading" class="mb-5">
                    <h2 id="technical-heading" class="h4 mb-3">
                        <span aria-hidden="true">⚙️</span> Technical Requirements & Recommendations
                    </h2>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h3 class="h6">Recommended Screen Readers</h3>
                            <ul>
                                <li>NVDA (Windows) - Free</li>
                                <li>JAWS (Windows) - Commercial</li>
                                <li>VoiceOver (macOS/iOS) - Built-in</li>
                                <li>TalkBack (Android) - Built-in</li>
                                <li>Orca (Linux) - Free</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h3 class="h6">Supported Browsers</h3>
                            <ul>
                                <li>Chrome/Chromium (Latest)</li>
                                <li>Firefox (Latest)</li>
                                <li>Safari (Latest)</li>
                                <li>Edge (Latest)</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- Feedback -->
                <section aria-labelledby="feedback-heading" class="mb-4">
                    <h2 id="feedback-heading" class="h4 mb-3">
                        <span aria-hidden="true">💬</span> Accessibility Feedback
                    </h2>
                    <div class="alert alert-primary" role="region">
                        <h3 class="alert-heading h6">Help Us Improve</h3>
                        <p class="mb-3">
                            We're committed to making Grassland Awakening accessible to everyone. If you encounter 
                            accessibility barriers or have suggestions for improvement, please let us know.
                        </p>
                        <p class="mb-0">
                            <strong>Contact Methods:</strong><br>
                            • Use the feedback form in your user profile<br>
                            • Report issues through the game's support system<br>
                            • Include your assistive technology details when reporting issues
                        </p>
                    </div>
                </section>

                <!-- Quick Tips -->
                <section aria-labelledby="tips-heading">
                    <h2 id="tips-heading" class="h4 mb-3">
                        <span aria-hidden="true">💡</span> Quick Tips for Better Experience
                    </h2>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <span aria-hidden="true" class="fs-3 text-primary">🔍</span>
                                    <h3 class="card-title h6 mt-2">Use Browser Zoom</h3>
                                    <p class="card-text small">Increase browser zoom to 200% for larger text and UI elements.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <span aria-hidden="true" class="fs-3 text-success">⌨️</span>
                                    <h3 class="card-title h6 mt-2">Learn Shortcuts</h3>
                                    <p class="card-text small">Memorize the Alt+H, Alt+V, Alt+A shortcuts for quick navigation.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <span aria-hidden="true" class="fs-3 text-info">🎧</span>
                                    <h3 class="card-title h6 mt-2">Audio Cues</h3>
                                    <p class="card-text small">Enable system sounds for additional audio feedback from the interface.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection