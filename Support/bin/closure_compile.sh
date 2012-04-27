#!/bin/bash
CLOSURE=$CLOSURE_PATH
echo "<pre>"
echo "Cleaning up..."
find "$CLOSURE/goog" -iname '._*' | xargs rm
echo "Calculating dependencies and compiling..."
python "$CLOSURE/bin/calcdeps.py" \
-e "$CLOSURE/goog/.AppleDouble" -e "$CLOSURE/goog/*/.AppleDouble" -e "$CLOSURE/goog/*/*/.AppleDouble" \
-e "$CLOSURE/goog/*/*/*/.AppleDouble" -e "$CLOSURE/goog/*/*/*/*/.AppleDouble" \
-i "$CLOSURE/../../main/www/js/config/closure.js" \
-i "$CLOSURE/goog/tmp.js" \
-p "$CLOSURE/goog" \
-o compiled \
-c "$CLOSURE/../../../closure_compiler/java/closure/build/compiler.jar" \
-f --warning_level=VERBOSE \
-f --define=goog.DEBUG=true \
-f --compilation_level=SIMPLE_OPTIMIZATIONS \
-f --define=goog.LOCALE=\'da\' \
-f --externs="$CLOSURE/../../main/www/js/EdulabI18nTranslation.js" \
-f --externs -f "$CLOSURE/goog/externs.js" \
-f --jscomp_warning=const \
-f --jscomp_warning=checkTypes \
-f --jscomp_warning=accessControls \
--output_file="$CLOSURE/goog/compiled.js" 2>&1 | cat > "$CLOSURE/goog/compile-bugs.txt"
echo "</pre>"
php "$TM_BUNDLE_SUPPORT/bin/closureOutputParse.php" "$CLOSURE/goog/compile-bugs.txt"