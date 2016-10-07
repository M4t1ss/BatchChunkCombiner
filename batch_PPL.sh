
FILE_PATH=$1;
FILE_TEMPLATE=$2;

echo -e '' > output_$FILE_TEMPLATE.txt

FILES=$FILE_PATH/*
for f in $FILES
do
  if [[ $f == *$FILE_TEMPLATE* ]] ; then
	echo $f >> output_$FILE_TEMPLATE.txt
	echo -e '\t' >> output_$FILE_TEMPLATE.txt
	/home/ubuntu/torch/install/bin/th /mnt/matiss/torch/char-rnn/measure_perplexity.lua $f \
	-data_path /mnt/matiss/data/dev.lv \
	-verbose 0 | grep "Perplexity per word:" >> output_$FILE_TEMPLATE.txt
	echo "" >> output_$FILE_TEMPLATE.txt
  fi
done









# T1="test";
# T2=".txt";
# TEMPFILE=$T1$RANDOM$T2;
# DATA="$1";

# echo $DATA > /home/matiss/tools/char-rnn/test/$TEMPFILE;

# /home/matiss/torch/install/bin/th /home/matiss/tools/char-rnn/measure_perplexity.lua /home/matiss/tools/char-rnn/cv/DGT_500k/lm_lstm_epoch7.03_0.8012.t7 -data_path /home/matiss/tools/char-rnn/test/$TEMPFILE -verbose 0 | grep "Perplexity per word:"
	
# rm /home/matiss/tools/char-rnn/test/$TEMPFILE;